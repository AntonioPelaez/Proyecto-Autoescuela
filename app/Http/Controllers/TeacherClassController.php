<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSession;

class TeacherClassController extends Controller
{
    /**
     * RESERVAS DE UN PROFESOR A UNO O VARIOS ALUMNOS (CLASES RESERVADAS)
     */
    public function reservasProfesor(Request $request)
{
    $user = $request->user();

    if ($user->role->name !== 'teacher') {
        return response()->json(['error' => 'Solo los profesores pueden ver sus reservas'], 403);
    }

    $teacher = $user->teacherProfile;

    $date      = $request->input('date');       // Para agenda
    $dateFrom  = $request->input('dateFrom');   // Para clases asignadas
    $dateTo    = $request->input('dateTo');     // Para clases asignadas
    $student   = $request->input('student');
    $status    = $request->input('status');

    $query = ClassSession::where('teacher_profile_id', $teacher->id)
        ->with(['studentProfile.user', 'town', 'vehicle']);

    // 🔥 PRIORIDAD: si hay rango de fechas, NO usar date
    if ($dateFrom) {
        $query->where('session_date', '>=', $dateFrom);
    }

    if ($dateTo) {
        $query->where('session_date', '<=', $dateTo);
    }

    // 🔥 Solo aplicar date si NO hay rango
    if (!$dateFrom && !$dateTo && $date) {
        $query->where('session_date', $date);
    }

    if ($student) {
        $query->whereHas('studentProfile.user', function ($q) use ($student) {
            $q->where('name', 'LIKE', "%$student%")
              ->orWhere('surname1', 'LIKE', "%$student%")
              ->orWhere('surname2', 'LIKE', "%$student%");
        });
    }

    if ($status) {
        $query->where('status', $status);
    }

    $reservas = $query
        ->orderBy('session_date')
        ->orderBy('start_time')
        ->get()
        ->map(function ($reserva) {
            return [
                'id'          => $reserva->id,
                'date'        => $reserva->session_date,
                'time'        => substr($reserva->start_time, 0, 5),
                'studentName' => $reserva->studentProfile->user->name . ' ' .
                                 $reserva->studentProfile->user->surname1 . ' ' .
                                 $reserva->studentProfile->user->surname2,
                'townName'    => $reserva->town->name ?? null,
                'vehicle'     => $reserva->vehicle->model ?? null,

                'status' => $reserva->status,
            ];
        });

    return response()->json([
        'reservas' => $reservas
    ]);
}


}
