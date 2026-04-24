<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSession;

class ClassSessionQueryController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'town_id' => 'required|integer',
            'date' => 'required|date',
        ]);

        $townId = $request->town_id;
        $date = $request->date;

        $user = auth()->user();

        // Detectar rol según relaciones
        $isStudent = $user->studentProfile()->exists();
        $isTeacher = $user->teacherProfile()->exists();

        $query = ClassSession::with([
            'teacherProfile.user',
            'studentProfile.user',
            'vehicle'
        ])
        ->where('town_id', $townId)
        ->where('session_date', $date)
        ->orderBy('slot_starts_at');

        /*
        |--------------------------------------------------------------------------
        | FILTRO DE PRIVACIDAD SEGÚN ROL
        |--------------------------------------------------------------------------
        */

        if ($isStudent) {
            // Un alumno solo ve SUS clases
            $query->where('student_profile_id', $user->studentProfile->id);
        }

        if ($isTeacher) {
            // Un profesor solo ve SUS clases
            $query->where('teacher_profile_id', $user->teacherProfile->id);
        }

        // Si es admin, no filtramos nada (ve todo)
        // Si no es admin, student o teacher, no debería ver nada
        if (!$isStudent && !$isTeacher && !$user->is_admin) {
            return response()->json([
                'town_id' => $townId,
                'date' => $date,
                'sessions' => []
            ]);
        }

        $sessions = $query->get();

        return response()->json([
            'town_id' => $townId,
            'date' => $date,
            'sessions' => $sessions
        ]);
    }
}
