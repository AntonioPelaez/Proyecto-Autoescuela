<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeacherProfile;
use App\Models\TeacherTown;
use App\Models\TeacherVehicle;
use App\Models\TeacherWeeklyAvailability;
use App\Models\ClassSession;
use App\Models\Town;
use Carbon\Carbon;

class TeacherAvailabilityController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | /api/teachers/{teacher}/availability
    |--------------------------------------------------------------------------
    | Devuelve los slots de un profesor específico para una fecha.
    |--------------------------------------------------------------------------
    */
    public function getAvailability($teacherId, Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = $request->date;

        /*
        |--------------------------------------------------------------------------
        | 1. Verificar que el profesor existe
        |--------------------------------------------------------------------------
        */
        $teacher = TeacherProfile::find($teacherId);

        if (!$teacher) {
            return response()->json([
                'teacher_id' => $teacherId,
                'date'       => $date,
                'slots'      => [],
                'error'      => 'Profesor no encontrado'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Pueblo asignado
        |--------------------------------------------------------------------------
        */
        $teacherTown = TeacherTown::where('teacher_profile_id', $teacherId)->first();

        if (!$teacherTown) {
            return response()->json([
                'teacher_id' => $teacherId,
                'date'       => $date,
                'slots'      => [],
                'error'      => 'El profesor no está asignado a ningún pueblo'
            ]);
        }

        $townId = $teacherTown->town_id;

        /*
        |--------------------------------------------------------------------------
        | 3. Disponibilidad semanal
        |--------------------------------------------------------------------------
        */
        $dayOfWeek = Carbon::parse($date)->dayOfWeekIso;

        $availability = TeacherWeeklyAvailability::where('teacher_profile_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (!$availability) {
            return response()->json([
                'teacher_id' => $teacherId,
                'town_id'    => $townId,
                'date'       => $date,
                'slots'      => [],
                'error'      => 'El profesor no tiene disponibilidad ese día'
            ]);
        }

        $start = Carbon::parse("$date {$availability->starts_time}");
        $end   = Carbon::parse("$date {$availability->end_time}");

        /*
        |--------------------------------------------------------------------------
        | 4. Vehículo asignado
        |--------------------------------------------------------------------------
        */
        $vehicle = TeacherVehicle::getVehicleForDate($teacherId, $date);

        if (!$vehicle) {
            return response()->json([
                'teacher_id' => $teacherId,
                'town_id'    => $townId,
                'date'       => $date,
                'slots'      => [],
                'error'      => 'El profesor no tiene vehículo asignado ese día'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Clases confirmadas (bloquean)
        |--------------------------------------------------------------------------
        */
        $existing = ClassSession::where('teacher_profile_id', $teacherId)
            ->where('session_date', $date)
            ->where('status', 'confirmed')
            ->pluck('slot_starts_at')
            ->map(fn($s) => Carbon::parse($s)->format('H:i'))
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | 6. Generar intervalos de 45 minutos
        |--------------------------------------------------------------------------
        */
        $slots = [];
        $cursor = $start->copy();

        while ($cursor->lt($end)) {
            $slotStart = $cursor->copy();
            $slotEnd   = $cursor->copy()->addMinutes(45);

            if ($slotEnd->lte($end)) {
                $hour = $slotStart->format('H:i');

                $slots[] = [
                    'start'      => $slotStart->format('Y-m-d H:i:s'),
                    'end'        => $slotEnd->format('Y-m-d H:i:s'),
                    'vehicle_id' => $vehicle->vehicle_id,
                    'reserved'   => in_array($hour, $existing),
                ];
            }

            $cursor->addMinutes(45);
        }

        return response()->json([
            'teacher_id' => $teacherId,
            'town_id'    => $townId,
            'date'       => $date,
            'slots'      => $slots,
            'source'     => 'live'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CRUD de Disponibilidades Semanales
    |--------------------------------------------------------------------------
    */

    /**
     * Listar todas las disponibilidades semanales con filtros opcionales
     * GET /api/teachers/weekly-availabilities
     */
    public function index(Request $request)
    {
        $query = TeacherWeeklyAvailability::query();

        // Filtrar por profesor
        if ($request->has('teacher_profile_id')) {
            $query->where('teacher_profile_id', $request->teacher_profile_id);
        }

        // Filtrar por pueblo
        if ($request->has('town_id')) {
            $query->where('town_id', $request->town_id);
        }

        // Filtrar por día de la semana
        if ($request->has('day_of_week')) {
            $query->where('day_of_week', $request->day_of_week);
        }

        // Filtrar por activo/inactivo
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $availabilities = $query->with(['teacher' => function ($q) {
            $q->select('id', 'user_id')->with('user:id,name,email');
        }, 'town:id,name'])->get();

        return response()->json([
            'data' => $availabilities,
            'count' => $availabilities->count()
        ]);
    }

    /**
     * Obtener una disponibilidad específica
     * GET /api/teachers/weekly-availabilities/{id}
     */
    public function show($id)
    {
        $availability = TeacherWeeklyAvailability::with([
            'teacher' => function ($q) {
                $q->select('id', 'user_id')->with('user:id,name,email');
            },
            'town:id,name'
        ])->find($id);

        if (!$availability) {
            return response()->json([
                'error' => 'Disponibilidad no encontrada'
            ], 404);
        }

        return response()->json($availability);
    }

    /**
     * Crear una nueva disponibilidad semanal
     * POST /api/teachers/weekly-availabilities
     *
     * Request body:
     * {
     *   "teacher_profile_id": 1,
     *   "town_id": 2,
     *   "day_of_week": 1,         // 0=Sunday, 1=Monday, ..., 6=Saturday (ISO-8601)
     *   "starts_time": "09:00:00",
     *   "end_time": "14:00:00",
     *   "slot_minutes": 60        // duración de cada slot (opcional, default 60)
     * }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_profile_id' => 'required|exists:teacher_profiles,id',
            'town_id' => 'required|exists:towns,id',
            'day_of_week' => 'required|integer|between:0,6',
            'starts_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:starts_time',
            'slot_minutes' => 'sometimes|integer|min:15|max:480',
        ]);

        // Validar que el profesor existe y está activo
        $teacher = TeacherProfile::find($validated['teacher_profile_id']);
        if (!$teacher || !$teacher->is_active_for_booking) {
            return response()->json([
                'error' => 'El profesor no existe o no está activo'
            ], 422);
        }

        // Validar que el pueblo existe
        $town = Town::find($validated['town_id']);
        if (!$town) {
            return response()->json([
                'error' => 'El pueblo no existe'
            ], 422);
        }

        // Verificar que no hay solapamiento de horarios en el mismo día
        $startTime = $validated['starts_time'];
        $endTime = $validated['end_time'];

        $overlap = TeacherWeeklyAvailability::where('teacher_profile_id', $validated['teacher_profile_id'])
            ->where('town_id', $validated['town_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where(function ($q) use ($startTime, $endTime) {
                // Hay solapamiento si: start_new < end_existing AND end_new > start_existing
                $q->whereRaw("? < end_time AND ? > starts_time", [$startTime, $endTime]);
            })
            ->first();

        if ($overlap) {
            return response()->json([
                'error' => 'El horario se solapa con una disponibilidad existente'
            ], 422);
        }

        // Crear la disponibilidad
        $availability = TeacherWeeklyAvailability::create($validated);

        return response()->json([
            'message' => 'Disponibilidad creada correctamente',
            'data' => $availability->load([
                'teacher' => function ($q) {
                    $q->select('id', 'user_id')->with('user:id,name,email');
                },
                'town:id,name'
            ])
        ], 201);
    }

    /**
     * Actualizar una disponibilidad existente
     * PUT /api/teachers/weekly-availabilities/{id}
     */
    public function update(Request $request, $id)
    {
        $availability = TeacherWeeklyAvailability::find($id);

        if (!$availability) {
            return response()->json([
                'error' => 'Disponibilidad no encontrada'
            ], 404);
        }

        $validated = $request->validate([
            'town_id' => 'sometimes|exists:towns,id',
            'day_of_week' => 'sometimes|integer|between:0,6',
            'starts_time' => 'sometimes|date_format:H:i:s',
            'end_time' => 'sometimes|date_format:H:i:s',
            'slot_minutes' => 'sometimes|integer|min:15|max:480',
            'is_active' => 'sometimes|boolean',
        ]);

        // Si actualiza hora de inicio o fin, validar que fin sea después del inicio
        if (isset($validated['starts_time']) || isset($validated['end_time'])) {
            $startTime = $validated['starts_time'] ?? $availability->starts_time;
            $endTime = $validated['end_time'] ?? $availability->end_time;

            if (strtotime($endTime) <= strtotime($startTime)) {
                return response()->json([
                    'error' => 'La hora de fin debe ser posterior a la de inicio'
                ], 422);
            }
        }

        $availability->update($validated);

        return response()->json([
            'message' => 'Disponibilidad actualizada correctamente',
            'data' => $availability->load([
                'teacher' => function ($q) {
                    $q->select('id', 'user_id')->with('user:id,name,email');
                },
                'town:id,name'
            ])
        ]);
    }

    /**
     * Eliminar una disponibilidad
     * DELETE /api/teachers/weekly-availabilities/{id}
     */
    public function destroy($id)
    {
        $availability = TeacherWeeklyAvailability::find($id);

        if (!$availability) {
            return response()->json([
                'error' => 'Disponibilidad no encontrada'
            ], 404);
        }

        $availability->delete();

        return response()->json([
            'message' => 'Disponibilidad eliminada correctamente'
        ]);
    }

    /**
     * Alternar estado activo/inactivo de una disponibilidad
     * POST /api/teachers/weekly-availabilities/{id}/toggle
     */
    public function toggle($id)
    {
        $availability = TeacherWeeklyAvailability::find($id);

        if (!$availability) {
            return response()->json([
                'error' => 'Disponibilidad no encontrada'
            ], 404);
        }

        $availability->update(['is_active' => !$availability->is_active]);

        return response()->json([
            'message' => 'Estado de disponibilidad actualizado',
            'is_active' => $availability->is_active,
            'data' => $availability->load([
                'teacher' => function ($q) {
                    $q->select('id', 'user_id')->with('user:id,name,email');
                },
                'town:id,name'
            ])
        ]);
    }
}
