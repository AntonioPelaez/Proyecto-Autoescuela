<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StudentProfile;
use Illuminate\Http\Request;

class StudentProfileController extends Controller
{
    /**
     * LISTADO DE ALUMNOS
     * Solo usuarios con rol alumno (role_id = 3)
     */
    public function index()
    {
        $students = StudentProfile::with('user')
            ->whereHas('user', function ($q) {
                $q->where('role_id', 3); // Solo alumnos
            })
            ->get();

        return view('students.index', compact('students'));
    }

    /**
     * FORMULARIO DE CREACIÓN
     * Usuarios con rol alumno que NO tengan perfil
     */
    public function create()
    {
        $users = User::where('role_id', 3)
            ->whereDoesntHave('studentProfile')
            ->get();

        return view('students.create', compact('users'));
    }

    /**
     * GUARDAR ALUMNO
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'dni' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'pickup_notes' => 'nullable|string|max:255',
        ]);

        StudentProfile::create([
            'user_id' => $request->user_id,
            'dni' => $request->dni,
            'birth_date' => $request->birth_date,
            'pickup_notes' => $request->pickup_notes,
        ]);

        return redirect()->route('students.index')->with('success', 'Alumno creado correctamente');
    }

    /**
     * FORMULARIO DE EDICIÓN
     */
    public function edit(StudentProfile $student)
    {
        return view('students.edit', compact('student'));
    }

    /**
     * ACTUALIZAR ALUMNO
     */
    public function update(Request $request, StudentProfile $student)
    {
        $request->validate([
            'dni' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'pickup_notes' => 'nullable|string|max:255',
        ]);

        $student->update([
            'dni' => $request->dni,
            'birth_date' => $request->birth_date,
            'pickup_notes' => $request->pickup_notes,
        ]);

        return redirect()->route('students.index')->with('success', 'Alumno actualizado correctamente');
    }

    /**
     * ELIMINAR ALUMNO
     */
    public function destroy(StudentProfile $student)
    {
        $student->delete();
        return redirect()->route('students.index')->with('success', 'Alumno eliminado correctamente');
    }

    /**
     * NOTAS DEL ALUMNO
     */
    public function notes(StudentProfile $student)
    {
        return view('students.notes', compact('student'));
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

        return redirect()->route('students.edit', $student)->with('success', 'Notas actualizadas');
    }
}
