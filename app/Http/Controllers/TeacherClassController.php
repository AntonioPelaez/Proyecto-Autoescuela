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

    // Validar que es profesor
    if ($user->role->name !== 'teacher') {
        return response()->json(['error' => 'Solo los profesores pueden ver sus reservas'], 403);
    }

    // Obtener el perfil del profesor
    $teacher = $user->teacherProfile;

    // Obtener reservas del profesor
    $reservas = ClassSession::where('teacher_profile_id', $teacher->id)
        ->with(['studentProfile.user'])
        ->orderBy('session_date')
        ->orderBy('slot_starts_at')
        ->get()
        ->map(function ($reserva) {
            return [
                'student_name'      => $reserva->studentProfile->user->name,
                'student_surname1'  => $reserva->studentProfile->user->surname1,
                'student_surname2'  => $reserva->studentProfile->user->surname2,
                'session_date'      => $reserva->session_date,
                'status'            => $reserva->status,
                'start_time'        => $reserva->slot_starts_at,
                'end_time'          => $reserva->slot_ends_at,
            ];
        });

    return response()->json([
        'teacher_id' => $teacher->id,
        'reservas'   => $reservas
    ]);
}



}
