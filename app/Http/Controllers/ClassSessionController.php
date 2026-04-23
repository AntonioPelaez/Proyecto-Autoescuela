<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSession;
use App\Models\TeacherProfile;
use App\Models\TeacherVehicle;
use App\Models\Town;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ClassSessionController extends Controller
{
    public function create()
    {
        $towns = Town::orderBy('name')->get();
        $teachers = TeacherProfile::with('user')->get();

        return view('class_sessions.create', compact('towns', 'teachers'));
    }

    public function hours(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|integer',
            'town_id' => 'required|integer',
            'date' => 'required|date',
        ]);

        $teacherId = $request->teacher_id;
        $townId = $request->town_id;
        $date = $request->date;

        // SIN festivos

        // Disponibilidad semanal
        $dayOfWeek = Carbon::parse($date)->dayOfWeekIso;

        $availability = \App\Models\TeacherWeeklyAvailability::where('teacher_profile_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (!$availability) {
            return response()->json(['hours' => []]);
        }

        $start = Carbon::parse("$date {$availability->start_time}");
        $end = Carbon::parse("$date {$availability->end_time}");

        // Vehículo asignado ese día
        $vehicle = TeacherVehicle::getVehicleForDate($teacherId, $date);

        if (!$vehicle) {
            return response()->json(['hours' => []]);
        }

        // Reservas existentes (guardamos las horas de inicio)
        $existing = ClassSession::where('teacher_profile_id', $teacherId)
            ->where('session_date', $date)
            ->pluck('slot_starts_at')
            ->map(fn($s) => Carbon::parse($s)->format('H:i'))
            ->toArray();

        // Generar intervalos de 45 minutos
        $intervals = [];
        $cursor = $start->copy();

        while ($cursor->lt($end)) {
            $slotStart = $cursor->copy();
            $slotEnd = $cursor->copy()->addMinutes(45);

            if ($slotEnd->lte($end)) {
                $hour = $slotStart->format('H:i');
                $isReserved = in_array($hour, $existing);

                $intervals[] = [
                    'start' => $slotStart->format('Y-m-d H:i:s'),
                    'end' => $slotEnd->format('Y-m-d H:i:s'),
                    'vehicle_id' => $vehicle->vehicle_id,
                    'reserved' => $isReserved,
                ];
            }

            $cursor->addMinutes(45);
        }

        return response()->json([
            'hours' => $intervals
        ]);
    }

    public function daySessions(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|integer',
            'date' => 'required|date',
        ]);

        $teacherId = $request->teacher_id;
        $date = $request->date;

        $sessions = ClassSession::with([
                'studentProfile.user',
                'teacherProfile.user',
                'vehicle'
            ])
            ->where('teacher_profile_id', $teacherId)
            ->where('session_date', $date)
            ->orderBy('slot_starts_at')
            ->get();

        return response()->json([
            'sessions' => $sessions
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|integer',
            'student_id' => 'required|integer',
            'town_id' => 'required|integer',
            'vehicle_id' => 'required|integer',
            'date' => 'required|date',
            'start' => 'required|date_format:Y-m-d H:i:s',
            'end' => 'required|date_format:Y-m-d H:i:s',
            'price' => 'nullable|numeric',
            'student_comments' => 'nullable|string',
        ]);

        $teacherId = $request->teacher_id;
        $studentId = $request->student_id;
        $townId = $request->town_id;
        $vehicleId = $request->vehicle_id;
        $date = $request->date;

        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        // SIN festivos

        if (!TeacherProfile::find($teacherId)) {
            return response()->json(['error' => 'Profesor no encontrado'], 404);
        }

        if (!TeacherVehicle::where('teacher_profile_id', $teacherId)->where('vehicle_id', $vehicleId)->exists()) {
            return response()->json(['error' => 'Vehículo no asignado al profesor'], 422);
        }

        // Solapamiento: tratamos intervalos como [start, end)
        $overlap = ClassSession::where('teacher_profile_id', $teacherId)
            ->where('session_date', $date)
            ->where(function ($q) use ($start, $end) {
                $q->where(function ($q2) use ($start, $end) {
                    $q2->where('slot_starts_at', '<', $end)
                       ->where('slot_ends_at', '>', $start);
                });
            })
            ->exists();

        if ($overlap) {
            return response()->json(['error' => 'Hora ocupada'], 422);
        }

        $session = ClassSession::create([
            'student_profile_id' => $studentId,
            'teacher_profile_id' => $teacherId,
            'town_id' => $townId,
            'vehicle_id' => $vehicleId,
            'session_date' => $date,
            'start_time' => $start->format('H:i:s'),
            'end_time' => $end->format('H:i:s'),
            'slot_starts_at' => $start,
            'slot_ends_at' => $end,
            'status' => 'confirmed',
            'payment_status' => 'pending',
            'price' => $request->price ?? 0,
            'booking_reference' => strtoupper(Str::random(10)),
            'student_comments' => $request->student_comments,
            'internal_notes' => null,
            'cancelled_at' => null,
        ]);

        return response()->json([
            'message' => 'Clase reservada correctamente',
            'session' => $session
        ]);
    }
}
