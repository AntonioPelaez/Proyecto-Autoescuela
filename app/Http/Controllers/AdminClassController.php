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
    $query = ClassSession::query()
    ->leftJoin('vehicles', 'vehicles.id', '=', 'class_sessions.vehicle_id')
    ->leftJoin('teacher_profiles', 'teacher_profiles.id', '=', 'class_sessions.teacher_profile_id')
    ->leftJoin('users as teacher_users', 'teacher_users.id', '=', 'teacher_profiles.user_id')
    ->leftJoin('student_profiles', 'student_profiles.id', '=', 'class_sessions.student_profile_id')
    ->leftJoin('users as student_users', 'student_users.id', '=', 'student_profiles.user_id')
    ->leftJoin('towns', 'towns.id', '=', 'class_sessions.town_id')
    ->select(
        'class_sessions.*',

        // VEHÍCULO
        'vehicles.brand as vehicle_brand',
        'vehicles.model as vehicle_model',
        'vehicles.plate_number as vehicle_plate',

        // PROFESOR (desde users)
        'teacher_users.name as teacher_name',
        'teacher_users.surname1 as teacher_surname1',
        'teacher_users.surname2 as teacher_surname2',

        // ALUMNO (desde users)
        'student_users.name as student_name',
        'student_users.surname1 as student_surname1',
        'student_users.surname2 as student_surname2',

        // POBLACIÓN
        'towns.name as town_name'
    )
    ->orderBy('class_sessions.session_date', 'asc')
    ->orderBy('class_sessions.start_time', 'asc');



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
