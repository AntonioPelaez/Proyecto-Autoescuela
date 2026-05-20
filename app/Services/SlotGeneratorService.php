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

        // 1. Disponibilidad semanal
        $weeklyAvailability = TeacherWeeklyAvailability::where('teacher_profile_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->get();

        if ($weeklyAvailability->isEmpty()) {
            return [];
        }

        // 2. Vehículo asignado
        $vehicleAssignment = TeacherVehicle::where('teacher_profile_id', $teacherId)
            ->where('starts_at', '<=', $date . ' 23:59:59')
            ->where('ends_at', '>=', $date . ' 00:00:00')
            ->orderBy('is_primary', 'desc')
            ->first();

        if (!$vehicleAssignment) {
            return [];
        }

        $vehicleId = $vehicleAssignment->vehicle_id;

        // 3. Reservas del día
        $reservations = ClassSession::where('teacher_profile_id', $teacherId)
            ->whereDate('start_at', $date)
            ->get();

        // 4. Excepciones del día
        $exceptions = TeacherAvailabilityException::where('teacher_profile_id', $teacherId)
            ->whereDate('date', $date)
            ->get();

        // 5. Generar slots
        $unique = []; // ← evita duplicados

        foreach ($weeklyAvailability as $availability) {

    $start = Carbon::parse($date . ' ' . $availability->starts_time);
    $end   = Carbon::parse($date . ' ' . $availability->end_time);

    $duration = $availability->slot_minutes;

   while ($start <= $end) {

    $slotStart = $start->copy();
    $slotEnd   = $start->copy()->addMinutes($duration);

    // si el slot se pasa del final → no lo generamos
    if ($slotEnd->gt($end)) {
        break;
    }

    // excepciones
    if ($exceptions->contains(function ($ex) use ($slotStart, $slotEnd) {
        return $slotStart->between($ex->start_at, $ex->end_at)
            || $slotEnd->between($ex->start_at, $ex->end_at);
    })) {
        $start->addMinutes($duration);
        continue;
    }

    // reservas
    if ($reservations->contains(function ($res) use ($slotStart, $slotEnd) {
        return $slotStart->between($res->start_at, $res->end_at)
            || $slotEnd->between($res->start_at, $res->end_at);
    })) {
        $start->addMinutes($duration);
        continue;
    }

    // guardar slot
    $unique[$slotStart->toDateTimeString()] = [
        'start'      => $slotStart->toDateTimeString(),
        'end'        => $slotEnd->toDateTimeString(),
        'vehicle_id' => $vehicleId,
    ];

    $start->addMinutes($duration);
}

}


        // 🔥 Convertir a array normal sin duplicados
        return array_values($unique);
    }
}
