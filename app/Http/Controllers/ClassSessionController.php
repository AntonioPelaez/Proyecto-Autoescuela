<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSession;
use App\Models\TeacherProfile;
use App\Models\TeacherVehicle;
use App\Models\Town;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
        $date = $request->date;

        $dayOfWeek = Carbon::parse($date)->dayOfWeekIso;

        $availability = \App\Models\TeacherWeeklyAvailability::where('teacher_profile_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (!$availability) {
            return response()->json(['hours' => []]);
        }

        $start = Carbon::parse("$date {$availability->start_time}");
        $end = Carbon::parse("$date {$availability->end_time}");

        $vehicle = TeacherVehicle::getVehicleForDate($teacherId, $date);
        if (!$vehicle) return response()->json(['hours' => []]);

        // SOLO bloqueamos clases confirmadas
        $existing = ClassSession::where('teacher_profile_id', $teacherId)
            ->where('session_date', $date)
            ->where('status', 'confirmed')
            ->pluck('slot_starts_at')
            ->map(fn($s) => Carbon::parse($s)->format('H:i'))
            ->toArray();

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

        return response()->json(['hours' => $intervals]);
    }

    public function daySessions(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|integer',
            'date' => 'nullable|date',
        ]);

        $teacherId = $request->teacher_id;
        $date = $request->date;

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
            'pending' => $sessions->where('status', 'pending')->values(),
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
        ]);

        return DB::transaction(function () use ($request) {

            $teacherId = $request->teacher_id;
            $date = $request->date;

            $start = Carbon::parse($request->start);
            $end = Carbon::parse($request->end);

            // Si hay una clase confirmada → NO permitir
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

            // Si hay una clase pendiente → cancelarla automáticamente
            ClassSession::where('teacher_profile_id', $teacherId)
                ->where('session_date', $date)
                ->where('status', 'pending')
                ->where(function ($q) use ($start, $end) {
                    $q->where('slot_starts_at', '<', $end)
                      ->where('slot_ends_at', '>', $start);
                })
                ->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now()
                ]);

            // Crear clase pendiente
            $session = ClassSession::create([
                'student_profile_id' => $request->student_id,
                'teacher_profile_id' => $teacherId,
                'town_id' => $request->town_id,
                'vehicle_id' => $request->vehicle_id,
                'session_date' => $date,
                'start_time' => $start->format('H:i:s'),
                'end_time' => $end->format('H:i:s'),
                'slot_starts_at' => $start,
                'slot_ends_at' => $end,
                'status' => 'pending',
                'payment_status' => 'pending',
                'booking_reference' => strtoupper(Str::random(10)),
            ]);

            return response()->json(['message' => 'Reserva pendiente creada', 'session' => $session]);
        });
    }

    public function cancel(Request $request)
    {
        $request->validate([
            'id' => 'required|integer'
        ]);

        return DB::transaction(function () use ($request) {

            $session = ClassSession::findOrFail($request->id);

            $session->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);

            return response()->json(['message' => 'Clase cancelada']);
        });
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'id' => 'required|integer'
        ]);

        return DB::transaction(function () use ($request) {

            $session = ClassSession::findOrFail($request->id);

            $session->update([
                'status' => 'confirmed',
                'payment_status' => 'confirmed'
            ]);

            return response()->json(['message' => 'Clase confirmada']);
        });
    }
}
