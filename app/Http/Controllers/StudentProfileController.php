<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StudentProfile;
use Illuminate\Http\Request;

class StudentProfileController extends Controller
{
    /**
     * LISTADO DE ALUMNOS (solo role_id = 3)
     */
    public function index()
    {
        $students = StudentProfile::with('user')
            ->whereHas('user', function ($q) {
                $q->where('role_id', 3);
            })
            ->get();

        return response()->json([
            'students' => $students
        ]);
    }

    /**
     * LISTA DE USUARIOS QUE PUEDEN SER ALUMNOS (role_id = 3 sin perfil)
     */
    public function availableUsers()
    {
        $users = User::where('role_id', 3)
            ->whereDoesntHave('studentProfile')
            ->get();

        return response()->json([
            'users' => $users
        ]);
    }

    /**
     * CREAR PERFIL DE ALUMNO
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id'      => 'required|exists:users,id',
            'dni'          => 'nullable|string|max:20',
            'birth_date'   => 'nullable|date',
            'pickup_notes' => 'nullable|string|max:255',
        ]);

        $student = StudentProfile::create([
            'user_id'      => $request->user_id,
            'dni'          => $request->dni,
            'birth_date'   => $request->birth_date,
            'pickup_notes' => $request->pickup_notes,
        ]);

        return response()->json([
            'message' => 'Alumno creado correctamente',
            'student' => $student
        ]);
    }

    /**
     * MOSTRAR UN ALUMNO
     */
    public function show(StudentProfile $student)
    {
        return response()->json([
            'student' => $student->load('user')
        ]);
    }

    /**
     * ACTUALIZAR ALUMNO
     */
    public function update(Request $request, StudentProfile $student)
    {
        $request->validate([
            'dni'          => 'nullable|string|max:20',
            'birth_date'   => 'nullable|date',
            'pickup_notes' => 'nullable|string|max:255',
        ]);

        $student->update([
            'dni'          => $request->dni,
            'birth_date'   => $request->birth_date,
            'pickup_notes' => $request->pickup_notes,
        ]);

        return response()->json([
            'message' => 'Alumno actualizado correctamente',
            'student' => $student
        ]);
    }

    /**
     * ELIMINAR ALUMNO
     */
    public function destroy(StudentProfile $student)
    {
        $student->delete();

        return response()->json([
            'message' => 'Alumno eliminado correctamente'
        ]);
    }

    /**
     * OBTENER NOTAS DEL ALUMNO
     */
    public function notes(StudentProfile $student)
    {
        return response()->json([
            'notes' => $student->pickup_notes
        ]);
    }

    /**
     * GUARDAR NOTAS DEL ALUMNO
     */
    public function saveNotes(Request $request, StudentProfile $student)
    {
        $request->validate([
            'pickup_notes' => 'nullable|string|max:255',
        ]);

        $student->update([
            'pickup_notes' => $request->pickup_notes,
        ]);

        return response()->json([
            'message' => 'Notas actualizadas correctamente',
            'student' => $student
        ]);
    }
}
