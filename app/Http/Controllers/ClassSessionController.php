<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSession;
use App\Models\TeacherProfile;
use App\Models\TeacherVehicle;
use App\Models\TeacherTown;
use App\Models\TeacherWeeklyAvailability;
use App\Services\SlotGeneratorService;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ClassSessionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Obtener horas disponibles (profesor individual)
    |--------------------------------------------------------------------------
    */
    public function hours(Request $request)
{
    $request->validate([
        'teacher_id' => 'required|integer',
        'town_id'    => 'required|integer',
        'date'       => 'required|date',
    ]);

    $teacherId = $request->teacher_id;
    $townId    = $request->town_id;
    $date      = Carbon::parse($request->date);
    $dayOfWeek = $date->dayOfWeek;

    // 🔥 1) OBTENER TODAS LAS DISPONIBILIDADES DEL DÍA
    $weekly = TeacherWeeklyAvailability::where('teacher_profile_id', $teacherId)
        ->where('town_id', $townId)
        ->where('day_of_week', $dayOfWeek)
        ->where('is_active', true)
        ->get();

    if ($weekly->isEmpty()) {
        return response()->json([
            'hours' => [],
            'total_classes' => 0 // 👈 también lo devolvemos aquí
        ]);
    }

    $hours = [];

    // 🔥 2) GENERAR HORAS PARA CADA BLOQUE
    foreach ($weekly as $block) {
        $slotMinutes = $block->slot_minutes ?? 45;

        $start = Carbon::parse($block->starts_time);
        $end   = Carbon::parse($block->end_time);

        while ($start->lt($end)) {
            $slotStart = $start->copy();
            $slotEnd   = $start->copy()->addMinutes($slotMinutes);

            if ($slotEnd->gt($end)) break;

            $hours[] = [
                'start'      => $slotStart->format('Y-m-d H:i:s'),
                'end'        => $slotEnd->format('Y-m-d H:i:s'),
                'reserved'   => false,
                'vehicle_id' => null,
            ];

            $start->addMinutes($slotMinutes);
        }
    }

    // 🔥 3) MARCAR HORAS RESERVADAS
    $reserved = ClassSession::where('teacher_profile_id', $teacherId)
        ->whereDate('session_date', $date)
        ->pluck('slot_starts_at')
        ->map(fn($s) => Carbon::parse($s)->format('Y-m-d H:i:s'))
        ->toArray();

    foreach ($hours as &$h) {
        if (in_array($h['start'], $reserved)) {
            $h['reserved'] = true;
        }
    }

    // 🔥 4) NUEVO: TOTAL DE CLASES IMPARTIDAS
    $totalClasses = ClassSession::where('teacher_profile_id', $teacherId)
        ->whereIn('status', ['confirmed', 'completed'])
        ->count();

    // 🔥 5) RESPUESTA FINAL
    return response()->json([
        'hours'         => $hours,
        'total_classes' => $totalClasses
    ]);
}




    /*
    |--------------------------------------------------------------------------
    | Obtener slots disponibles (pueblo → profesores → horas)
    |--------------------------------------------------------------------------
    */
    public function availabilitySlots(Request $request)
{
    $request->validate([
        'town_id' => 'required|integer',
        'date'    => 'required|date',
    ]);

    $townId    = $request->town_id;
    $date      = Carbon::parse($request->date);
    $dayOfWeek = $date->dayOfWeek; // IMPORTANTE: usar 0–6 igual que en weekly availability

    // Profesores asignados al pueblo (solo activos para reservas)
    $teachers = TeacherTown::whereHas('teacherProfile', function ($query) {
            $query->where('is_active_for_booking', 1);
        })
        ->where('town_id', $townId)
        ->pluck('teacher_profile_id');

    if ($teachers->isEmpty()) {
        return response()->json([
            'date'  => $date->toDateString(),
            'slots' => []
        ]);
    }

    $result = [];

    foreach ($teachers as $teacherId) {

        // 🔥 Obtener TODAS las disponibilidades del día (NO solo la primera)
        $availabilities = TeacherWeeklyAvailability::where('teacher_profile_id', $teacherId)
            ->where('town_id', $townId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->get();

        if ($availabilities->isEmpty()) {
            continue;
        }

        // Vehículo válido
        $vehicle = TeacherVehicle::getVehicleForDate($teacherId, $date->toDateString());
        if (!$vehicle) {
            continue;
        }

        // Clases reservadas
        $reserved = ClassSession::where('teacher_profile_id', $teacherId)
            ->where('session_date', $date->toDateString())
            ->pluck('slot_starts_at')
            ->map(fn($s) => Carbon::parse($s)->format('H:i'))
            ->toArray();

        $slots = [];

        // 🔥 Generar slots para CADA disponibilidad del día
        foreach ($availabilities as $availability) {

            $slotMinutes = $availability->slot_minutes ?? 45;

            $cursor = Carbon::parse($date->toDateString() . ' ' . $availability->starts_time);
            $end    = Carbon::parse($date->toDateString() . ' ' . $availability->end_time);

            while ($cursor->lt($end)) {
                $slotStart = $cursor->copy();
                $slotEnd   = $cursor->copy()->addMinutes($slotMinutes);

                if ($slotEnd->lte($end)) {
                    $hour = $slotStart->format('H:i');

                    $slots[] = [
                        'start'      => $slotStart->format('Y-m-d H:i:s'),
                        'end'        => $slotEnd->format('Y-m-d H:i:s'),
                        'vehicle_id' => $vehicle->vehicle_id,
                        'reserved'   => in_array($hour, $reserved),
                    ];
                }

                $cursor->addMinutes($slotMinutes);
            }
        }

        $result[] = [
            'teacher_id' => $teacherId,
            'vehicle_id' => $vehicle->vehicle_id,
            'slots'      => $slots,
        ];
    }

    return response()->json([
        'date'  => $date->toDateString(),
        'slots' => $result,
    ]);
}


    /*
    |--------------------------------------------------------------------------
    | Consultar clases del día
    |--------------------------------------------------------------------------
    */
    public function daySessions(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|integer',
            'date'       => 'nullable|date',
        ]);

        $teacherId = $request->teacher_id;
        $date      = $request->date;

        $query = ClassSession::with([
                'studentProfile.user',
                'teacherProfile.user',
                'vehicle'
            ])
            ->where('teacher_profile_id', $teacherId);

        if ($date) {
            $query->where('session_date', $date);
        } else {
            $query->where('session_date', '>=', now()->format('Y-m-d'));
        }

        $sessions = $query->orderBy('slot_starts_at')->get();

        return response()->json([
            'confirmed' => $sessions->where('status', 'confirmed')->values(),
            'pending'   => $sessions->where('status', 'pending')->values(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Crear reserva
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|integer',
            'student_id' => 'required|integer',
            'town_id'    => 'required|integer',
            'vehicle_id' => 'required|integer',
            'date'       => 'required|date',
            'start'      => 'required|date_format:Y-m-d H:i:s',
            'end'        => 'required|date_format:Y-m-d H:i:s',
        ]);

        return DB::transaction(function () use ($request) {

            $teacherId = $request->teacher_id;
            $date      = $request->date;

            $start = Carbon::parse($request->start);
            $end   = Carbon::parse($request->end);

            // Clases confirmadas solapadas
            $overlap = ClassSession::where('teacher_profile_id', $teacherId)
                ->where('session_date', $date)
                ->where('status', 'confirmed')
                ->where(function ($q) use ($start, $end) {
                    $q->where('slot_starts_at', '<', $end)
                      ->where('slot_ends_at', '>', $start);
                })
                ->exists();

            if ($overlap) {
                return response()->json(['error' => 'Hora ocupada'], 422);
            }

            // Cancelar pendientes solapadas
            ClassSession::where('teacher_profile_id', $teacherId)
                ->where('session_date', $date)
                ->where('status', 'pending')
                ->where(function ($q) use ($start, $end) {
                    $q->where('slot_starts_at', '<', $end)
                      ->where('slot_ends_at', '>', $start);
                })
                ->update([
                    'status'       => 'cancelled',
                    'cancelled_at' => now()
                ]);

            // Crear clase pendiente
            $session = ClassSession::create([
                'student_profile_id' => $request->student_id,
                'teacher_profile_id' => $teacherId,
                'town_id'            => $request->town_id,
                'vehicle_id'         => $request->vehicle_id,
                'session_date'       => $date,
                'start_time'         => $start->format('H:i:s'),
                'end_time'           => $end->format('H:i:s'),
                'slot_starts_at'     => $start,
                'slot_ends_at'       => $end,
                'status'             => 'pending',
                'payment_status'     => 'pending',
                'booking_reference'  => strtoupper(Str::random(10)),
            ]);

            return response()->json([
                'message' => 'Reserva pendiente creada',
                'session' => $session
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Cancelar reserva
    |--------------------------------------------------------------------------
    */
    public function cancel(Request $request)
    {
        $request->validate([
            'id' => 'required|integer'
        ]);

        return DB::transaction(function () use ($request) {

            $session = ClassSession::findOrFail($request->id);

            $session->update([
                'status'       => 'cancelled',
                'cancelled_at' => now()
            ]);

            return response()->json(['message' => 'Clase cancelada']);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Confirmar reserva
    |--------------------------------------------------------------------------
    */
    public function confirm(Request $request)
    {
        $request->validate([
            'id' => 'required|integer'
        ]);

        return DB::transaction(function () use ($request) {

            $session = ClassSession::findOrFail($request->id);

            $session->update([
                'status'         => 'confirmed',
                'payment_status' => 'confirmed'
            ]);

            return response()->json(['message' => 'Clase confirmada']);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Reasignar profesor a una clase reservada
    |--------------------------------------------------------------------------
    */
    public function reassignTeacher(Request $request)
    {
        $request->validate([
            'class_session_id' => 'required|integer|exists:class_sessions,id',
            'teacher_id'       => 'required|integer|exists:teacher_profiles,id',
        ]);

        $classSession = ClassSession::findOrFail($request->class_session_id);
        
        // Verificar que el nuevo profesor esté activo para reservas (0 = activo)
        $teacher = TeacherProfile::findOrFail($request->teacher_id);
        if ($teacher->is_active_for_booking) {
            return response()->json(['error' => 'El profesor no está activo para reservas'], 422);
        }

        // Verificar que el profesor tenga disponibilidad semanal
        $hasWeeklyAvailability = TeacherWeeklyAvailability::where('teacher_profile_id', $request->teacher_id)
            ->exists();
        
        if (!$hasWeeklyAvailability) {
            return response()->json(['error' => 'El profesor no tiene disponibilidad registrada'], 422);
        }

        // Actualizar profesor en la clase
        $classSession->update(['teacher_profile_id' => $request->teacher_id]);
        
        return response()->json([
            'message' => 'Profesor reasignado correctamente',
            'class_session' => $classSession->load('teacherProfile.user', 'studentProfile.user')
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Marcar clase como completada
    |--------------------------------------------------------------------------
    */
    public function complete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:class_sessions,id'
        ]);

        return DB::transaction(function () use ($request) {

            $session = ClassSession::findOrFail($request->id);

            // Verificar que la clase esté confirmada antes de marcarla como completada
            if ($session->status !== 'confirmed') {
                return response()->json([
                    'error' => 'Solo se pueden marcar como completadas las clases confirmadas'
                ], 422);
            }

            $session->update([
                'status' => 'completed'
            ]);

            return response()->json([
                'message' => 'Clase marcada como completada',
                'session' => $session->load('studentProfile.user', 'teacherProfile.user', 'vehicle', 'town')
            ]);
        });
    }
}
