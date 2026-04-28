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
            'date'    => 'required|date',
        ]);

        $townId = $request->town_id;
        $date   = $request->date;

        $user = auth()->user();

        // Cargar relaciones antes de usarlas
        $user->load(['studentProfile', 'teacherProfile']);

        $isStudent = $user->studentProfile !== null;
        $isTeacher = $user->teacherProfile !== null;
        $isAdmin   = $user->is_admin ?? false;

        $query = ClassSession::with([
                'teacherProfile.user',
                'studentProfile.user',
                'vehicle'
            ])
            ->where('town_id', $townId)
            ->where('session_date', $date)
            ->orderBy('slot_starts_at');

        // FILTRO POR ROL
        if ($isStudent) {
            $query->where('student_profile_id', $user->studentProfile->id);
        }

        if ($isTeacher) {
            $query->where('teacher_profile_id', $user->teacherProfile->id);
        }

        if (!$isStudent && !$isTeacher && !$isAdmin) {
            return response()->json([
                'town_id'  => $townId,
                'date'     => $date,
                'sessions' => []
            ]);
        }

        $sessions = $query->get();

        return response()->json([
            'town_id'  => $townId,
            'date'     => $date,
            'sessions' => $sessions
        ]);
    }
}
