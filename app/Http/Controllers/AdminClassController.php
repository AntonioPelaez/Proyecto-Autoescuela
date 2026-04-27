<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassSession;

class AdminClassController extends Controller
{
    /**
     * Mostrar todas las sesiones de clase.
     */
    public function index(Request $request)
    {
        $query = ClassSession::select(
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
            'tu.surname2 as teacher_surname2',
            'su.name as student_name',
            'su.surname1 as student_surname1',
            'su.surname2 as student_surname2'
        )
            ->join('towns', 'towns.id', '=', 'class_sessions.town_id')
            ->join('teacher_profiles as tp', 'tp.id', '=', 'class_sessions.teacher_profile_id')
            ->join('users as tu', 'tu.id', '=', 'tp.user_id')
            ->join('student_profiles as sp', 'sp.id', '=', 'class_sessions.student_profile_id')
            ->join('users as su', 'su.id', '=', 'sp.user_id');

        // FILTRO POR PROFESOR
        if ($request->has('teacher_id')) {
            $query->where('class_sessions.teacher_profile_id', $request->teacher_id);
        }

        // FILTRO POR POBLACIÓN
        if ($request->has('town_id')) {
            $query->where('class_sessions.town_id', $request->town_id);
        }

        // FILTRO POR FECHA
        if ($request->has('date')) {
            $query->where('class_sessions.session_date', $request->date);
        }

        $classes = $query
            ->orderBy('class_sessions.session_date')
            ->orderBy('class_sessions.start_time')
            ->get();

        return response()->json($classes);
    }
}
