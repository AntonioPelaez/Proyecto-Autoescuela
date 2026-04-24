<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSession;

class ClassController extends Controller
{
public function index(Request $request)
{
    // Obtener el perfil de alumno del usuario autenticado
    $student = $request->user()->studentProfile;

    if (!$student) {
        return response()->json(['message' => 'No eres alumno'], 403);
    }

    $classes = ClassSession::select(
            'class_sessions.id',
            'class_sessions.session_date',
            'class_sessions.start_time',
            'class_sessions.end_time',
            'class_sessions.status',
            'class_sessions.payment_status',
            'towns.name as town_name',
            'tp.id as teacher_profile_id',
            'tu.name as teacher_name',
            'tu.surname1 as teacher_surname1',
            'tu.surname2 as teacher_surname2'
        )
        ->join('towns', 'towns.id', '=', 'class_sessions.town_id')
        ->join('teacher_profiles as tp', 'tp.id', '=', 'class_sessions.teacher_profile_id')
        ->join('users as tu', 'tu.id', '=', 'tp.user_id')
        ->where('class_sessions.student_profile_id', $student->id)
        ->orderBy('class_sessions.session_date')
        ->orderBy('class_sessions.start_time')
        ->get();

    return response()->json($classes);
}


}
