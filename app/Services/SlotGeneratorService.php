<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\TeacherProfile;
use App\Models\TeacherWeeklyAvailability;
use App\Models\TeacherVehicle;
use App\Models\ClassSession;
use App\Models\TeacherAvailabilityException;

class SlotGeneratorService
{
    /**
     * Genera los slots disponibles para un profesor en una fecha concreta.
     */
    public function generateSlots(int $teacherId, string $date)
    {
        $teacher = TeacherProfile::findOrFail($teacherId);

        // Día de la semana (0 = domingo, 6 = sábado)
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        // 1. Obtener disponibilidad semanal del profesor para ese día
        $weeklyAvailability = TeacherWeeklyAvailability::where('teacher_profile_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->get();

        if ($weeklyAvailability->isEmpty()) {
            return []; // No trabaja ese día
        }

        // 2. Obtener vehículo asignado en esa fecha
        $vehicleAssignment = TeacherVehicle::where('teacher_profile_id', $teacherId)
            ->where('starts_at', '<=', $date . ' 23:59:59')
            ->where('ends_at', '>=', $date . ' 00:00:00')
            ->orderBy('is_primary', 'desc')
            ->first();

        if (!$vehicleAssignment) {
            return []; // No tiene vehículo asignado ese día
        }

        $vehicleId = $vehicleAssignment->vehicle_id;

        // 3. Obtener reservas existentes del profesor ese día
        $reservations = ClassSession::where('teacher_profile_id', $teacherId)
            ->whereDate('start_at', $date)
            ->get();

        // 4. Obtener excepciones (bloqueos)
        $exceptions = TeacherAvailabilityException::where('teacher_profile_id', $teacherId)
            ->whereDate('date', $date)
            ->get();

        // 5. Generar slots
        $slots = [];

        foreach ($weeklyAvailability as $availability) {

            $start = Carbon::parse($date . ' ' . $availability->start_time);
            $end = Carbon::parse($date . ' ' . $availability->end_time);

            // Duración de clase (en minutos)
            $duration = $availability->class_duration ?? 60;

            while ($start->copy()->addMinutes($duration) <= $end) {

                $slotStart = $start->copy();
                $slotEnd = $start->copy()->addMinutes($duration);

                // 5.1 Comprobar excepciones
                if ($exceptions->contains(function ($ex) use ($slotStart, $slotEnd) {
                    return $slotStart->between($ex->start_at, $ex->end_at)
                        || $slotEnd->between($ex->start_at, $ex->end_at);
                })) {
                    $start->addMinutes($duration);
                    continue;
                }

                // 5.2 Comprobar reservas
                if ($reservations->contains(function ($res) use ($slotStart, $slotEnd) {
                    return $slotStart->between($res->start_at, $res->end_at)
                        || $slotEnd->between($res->start_at, $res->end_at);
                })) {
                    $start->addMinutes($duration);
                    continue;
                }

                // 5.3 Slot válido → añadirlo
                $slots[] = [
                    'start' => $slotStart->toDateTimeString(),
                    'end' => $slotEnd->toDateTimeString(),
                    'vehicle_id' => $vehicleId,
                ];

                $start->addMinutes($duration);
            }
        }

        return $slots;
    }
}
