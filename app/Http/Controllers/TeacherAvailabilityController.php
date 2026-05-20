<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeacherProfile;
use App\Models\TeacherTown;
use App\Models\TeacherVehicle;
use App\Models\TeacherWeeklyAvailability;
use App\Models\ClassSession;
use App\Models\TeacherAvailabilityException;
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
    public function getAvailability(int $teacherId, Request $request)
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


        /**
         * Verificar si hay excepciones para ese día (vacaciones, enfermedad, etc.)
         */
        $exception = TeacherAvailabilityException::where('teacher_profile_id', $teacherId)
            ->where('exception_date', $date)
            ->where('type', 'especial')
            ->first();

        if ($exception) {
            $now = Carbon::now();

            // Si la excepción ya pasó, no mostrar disponibilidad
            if ($now->gt(Carbon::parse($exception->end_time))) {
                return response()->json([
                    'teacher_id' => $teacherId,
                    'town_id'    => $townId,
                    'date'       => $date,
                    'slots'      => [],
                    'source'     => 'exception-expired'
                ]);
            }

            // Si la excepción está vigente → usar sus horas en vez de la semanal
            $availability->starts_time = $exception->starts_time;
            $availability->end_time    = $exception->end_time;
        }

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
    // 1) ELIMINAR AUTOMÁTICAMENTE EXCEPCIONES CADUCADAS
    TeacherAvailabilityException::where('exception_date', '<', now()->toDateString())->delete();

    // 2) OBTENER DISPONIBILIDADES NORMALES
    $weekly = TeacherWeeklyAvailability::query()
        ->when($request->teacher_profile_id, fn($q) => $q->where('teacher_profile_id', $request->teacher_profile_id))
        ->when($request->town_id, fn($q) => $q->where('town_id', $request->town_id))
        ->when($request->day_of_week, fn($q) => $q->where('day_of_week', $request->day_of_week))
        ->when($request->has('is_active'), fn($q) => $q->where('is_active', $request->boolean('is_active')))
        ->with(['teacher.user:id,name,email', 'town:id,name'])
        ->get()
        ->map(function ($item) {
            $item->type = 'normal';
            return $item;
        });

    // 3) OBTENER DISPONIBILIDADES ESPECIALES
    $special = TeacherAvailabilityException::query()
        ->when($request->teacher_profile_id, fn($q) => $q->where('teacher_profile_id', $request->teacher_profile_id))
        ->when($request->town_id, fn($q) => $q->where('town_id', $request->town_id))
        ->with(['teacher.user:id,name,email', 'town:id,name'])
        ->get()
        ->map(function ($item) {
            $item->day_of_week = null; // no aplica
            $item->is_active = true;
            return $item;
        });

    // 4) UNIR AMBAS
    $data = $weekly->merge($special);

    return response()->json([
        'data' => $data,
        'count' => $data->count()
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
    $type = $request->input('type', 'normal'); // por defecto normal

    if ($type === 'especial') {

        // VALIDACIÓN PARA ESPECIAL
        $validated = $request->validate([
            'teacher_profile_id' => 'required|exists:teacher_profiles,id',
            'town_id' => 'required|exists:towns,id',
            'exception_date' => 'required|date',
            'starts_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:starts_time',
            'reason' => 'nullable|string|max:150',
            'type' => 'required|in:especial',
            'slot_minutes' => 'sometimes|integer|min:15|max:480',
        ]);

        // Crear excepción
        $exception = TeacherAvailabilityException::create([
            'teacher_profile_id' => $validated['teacher_profile_id'],
            'town_id' => $validated['town_id'],
            'exception_date' => $validated['exception_date'],
            'starts_time' => $validated['starts_time'],
            'end_time' => $validated['end_time'],
            'type' => 'especial',
            'reason' => $validated['reason'] ?? null,
        ]);

        return response()->json([
            'message' => 'Disponibilidad especial creada correctamente',
            'data' => $exception
        ], 201);
    }

    // VALIDACIÓN PARA NORMAL
    $validated = $request->validate([
        'teacher_profile_id' => 'required|exists:teacher_profiles,id',
        'town_id' => 'required|exists:towns,id',
        'day_of_week' => 'required|integer|between:0,6',
        'starts_time' => 'required|date_format:H:i:s',
        'end_time' => 'required|date_format:H:i:s|after:starts_time',
        'slot_minutes' => 'sometimes|integer|min:15|max:480',
        'type' => 'nullable|in:normal'
    ]);

    // Validar solapamientos SOLO para normal
    $overlap = TeacherWeeklyAvailability::where('teacher_profile_id', $validated['teacher_profile_id'])
        ->where('town_id', $validated['town_id'])
        ->where('day_of_week', $validated['day_of_week'])
        ->where(function ($q) use ($validated) {
            $q->whereRaw("? < end_time AND ? > starts_time", [
                $validated['starts_time'],
                $validated['end_time']
            ]);
        })
        ->first();

    if ($overlap) {
        return response()->json([
            'error' => 'El horario se solapa con una disponibilidad existente'
        ], 422);
    }

    // Crear disponibilidad normal
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
    $type = $request->input('type', 'normal');

    if ($type === 'especial') {

        $exception = TeacherAvailabilityException::find($id);

        if (!$exception) {
            return response()->json(['error' => 'Disponibilidad especial no encontrada'], 404);
        }

        $validated = $request->validate([
            'teacher_profile_id' => 'required|exists:teacher_profiles,id',
            'town_id' => 'nullable|exists:towns,id',
            'exception_date' => 'required|date',
            'starts_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:starts_time',
            'reason' => 'nullable|string|max:150',
            'type' => 'required|in:especial'
        ]);

        $exception->update($validated);

        return response()->json([
            'message' => 'Disponibilidad especial actualizada correctamente',
            'data' => $exception
        ]);
    }

    // ---- NORMAL ----
    $availability = TeacherWeeklyAvailability::find($id);

    if (!$availability) {
        return response()->json(['error' => 'Disponibilidad normal no encontrada'], 404);
    }

    $validated = $request->validate([
        'teacher_profile_id' => 'required|exists:teacher_profiles,id',
        'town_id' => 'required|exists:towns,id',
        'day_of_week' => 'required|integer|between:0,6',
        'starts_time' => 'required|date_format:H:i:s',
        'end_time' => 'required|date_format:H:i:s|after:starts_time',
        'slot_minutes' => 'sometimes|integer|min:15|max:480',
        'type' => 'nullable|in:normal'
    ]);

    $availability->update($validated);

    return response()->json([
        'message' => 'Disponibilidad normal actualizada correctamente',
        'data' => $availability
    ]);
}


    /**
     * Eliminar una disponibilidad
     * DELETE /api/teachers/weekly-availabilities/{id}
     */
    public function destroy(int $id)
{
    // Intentar eliminar normal
    $normal = TeacherWeeklyAvailability::find($id);
    if ($normal) {
        $normal->delete();
        return response()->json(['message' => 'Disponibilidad normal eliminada']);
    }

    // Intentar eliminar especial
    $special = TeacherAvailabilityException::find($id);
    if ($special) {
        $special->delete();
        return response()->json(['message' => 'Disponibilidad especial eliminada']);
    }

    return response()->json(['error' => 'Disponibilidad no encontrada'], 404);
}


    /**
     * Alternar estado activo/inactivo de una disponibilidad
     * POST /api/teachers/weekly-availabilities/{id}/toggle
     */
    public function toggle(int $id)
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
