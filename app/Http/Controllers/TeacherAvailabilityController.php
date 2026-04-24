<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeacherProfile;
use App\Models\TeacherTown;
use App\Models\TeacherVehicle;
use App\Models\TeacherWeeklyAvailability;
use App\Models\ClassSession;
use Carbon\Carbon;

class TeacherAvailabilityController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | NUEVO ENDPOINT: /api/teachers/{teacher}/availability
    |--------------------------------------------------------------------------
    | Devuelve los mismos slots que /api/availability-hours
    | pero filtrados por profesor.
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
                'date' => $date,
                'slots' => [],
                'error' => 'Profesor no encontrado'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Obtener el pueblo donde trabaja ese profesor
        |--------------------------------------------------------------------------
        */
        $teacherTown = TeacherTown::where('teacher_profile_id', $teacherId)->first();

        if (!$teacherTown) {
            return response()->json([
                'teacher_id' => $teacherId,
                'date' => $date,
                'slots' => [],
                'error' => 'El profesor no está asignado a ningún pueblo'
            ]);
        }

        $townId = $teacherTown->town_id;

        /*
        |--------------------------------------------------------------------------
        | 3. Obtener disponibilidad semanal
        |--------------------------------------------------------------------------
        */
        $dayOfWeek = Carbon::parse($date)->dayOfWeekIso;

        $availability = TeacherWeeklyAvailability::where('teacher_profile_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (!$availability) {
            return response()->json([
                'teacher_id' => $teacherId,
                'date' => $date,
                'slots' => [],
                'error' => 'El profesor no tiene disponibilidad ese día'
            ]);
        }

        $start = Carbon::parse("$date {$availability->start_time}");
        $end = Carbon::parse("$date {$availability->end_time}");

        /*
        |--------------------------------------------------------------------------
        | 4. Obtener vehículo asignado ese día
        |--------------------------------------------------------------------------
        */
        $vehicle = TeacherVehicle::getVehicleForDate($teacherId, $date);

        if (!$vehicle) {
            return response()->json([
                'teacher_id' => $teacherId,
                'date' => $date,
                'slots' => [],
                'error' => 'El profesor no tiene vehículo asignado ese día'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Obtener clases confirmadas (bloquean)
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
            $slotEnd = $cursor->copy()->addMinutes(45);

            if ($slotEnd->lte($end)) {
                $hour = $slotStart->format('H:i');
                $isReserved = in_array($hour, $existing);

                $slots[] = [
                    'start' => $slotStart->format('Y-m-d H:i:s'),
                    'end' => $slotEnd->format('Y-m-d H:i:s'),
                    'vehicle_id' => $vehicle->vehicle_id,
                    'reserved' => $isReserved,
                ];
            }

            $cursor->addMinutes(45);
        }

        return response()->json([
            'teacher_id' => $teacherId,
            'town_id' => $townId,
            'date' => $date,
            'slots' => $slots,
            'source' => 'live'
        ]);
    }
}
