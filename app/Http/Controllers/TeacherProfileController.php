<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Town;
use App\Models\TeacherProfile;
use Illuminate\Http\Request;

class TeacherProfileController extends Controller
{
    /**
     * LISTADO DE PROFESORES
     * Solo usuarios con rol profesor (role_id = 2)
     */
    public function index()
    {
        $teachers = TeacherProfile::with(['user', 'town'])
            ->whereHas('user', function ($q) {
                $q->where('role_id', 2); // Solo profesores
            })
            ->get();

        return view('teachers.index', compact('teachers'));
    }

    /**
     * FORMULARIO DE CREACIÓN
     * - Usuarios con rol profesor que NO tengan perfil
     * - Todas las poblaciones
     */
    public function create()
    {
        $users = User::where('role_id', 2)
            ->whereDoesntHave('teacherProfile')
            ->get();

        $towns = Town::all();

        return view('teachers.create', compact('users', 'towns'));
    }

    /**
     * GUARDAR PROFESOR
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'dni' => 'nullable|string|max:20',
            'license_number' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'is_active_for_booking' => 'nullable|boolean',
            'towns' => 'nullable|array',
        ]);

        // Crear perfil
        $teacher = TeacherProfile::create([
            'user_id' => $request->user_id,
            'dni' => $request->dni,
            'license_number' => $request->license_number,
            'notes' => $request->notes,
            'is_active_for_booking' => $request->is_active_for_booking ? 1 : 0,
        ]);

        // Asignar poblaciones
        if ($request->towns) {
            $teacher->town()->sync($request->towns);
        }

        return redirect()->route('teachers.index')->with('success', 'Profesor creado correctamente');
    }

    /**
     * FORMULARIO DE EDICIÓN
     */
    public function edit(TeacherProfile $teacher)
    {
        $towns = Town::all();
        return view('teachers.edit', compact('teacher', 'towns'));
    }

    /**
     * ACTUALIZAR PROFESOR
     */
    public function update(Request $request, TeacherProfile $teacher)
    {
        $request->validate([
            'dni' => 'nullable|string|max:20',
            'license_number' => 'required|string|max:50',
            'is_active_for_booking' => 'nullable|boolean',
            'towns' => 'nullable|array',
        ]);

        // Actualizar datos
        $teacher->update([
            'dni' => $request->dni,
            'license_number' => $request->license_number,
            'is_active_for_booking' => $request->is_active_for_booking ? 1 : 0,
        ]);

        // Actualizar poblaciones
        $teacher->town()->sync($request->towns ?? []);

        return redirect()->route('teachers.index')->with('success', 'Profesor actualizado correctamente');
    }

    /**
     * ELIMINAR PROFESOR
     */
    public function destroy(TeacherProfile $teacher)
    {
        $teacher->delete();
        return redirect()->route('teachers.index')->with('success', 'Profesor eliminado correctamente');
    }

    /**
     * PÁGINA DE NOTAS
     */
    public function notes(TeacherProfile $teacher)
    {
        return view('teachers.notes', compact('teacher'));
    }

    /**
     * GUARDAR NOTAS
     */
    public function saveNotes(Request $request, TeacherProfile $teacher)
    {
        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $teacher->update([
            'notes' => $request->notes,
        ]);

        return redirect()->route('teachers.edit', $teacher)->with('success', 'Notas actualizadas');
    }

    /**
     * ASOCIAR ALUMNOS
     */
    /**
     *  Mostrar alumnos disponibles 
     */
    public function students(TeacherProfile $teacher)
    {
        $availableStudents = StudentProfile::whereNull('teacher_id')->with('user')->get();
        $assignedStudents = StudentProfile::where('teacher_id', $teacher->id)->with('user')->get();

        return view('teachers.students', compact('teacher', 'availableStudents', 'assignedStudents'));
    }

    /**
     * Asociar alumno al profesor
     */
    public function attachStudent(Request $request, TeacherProfile $teacher)
    {
        $request->validate([
            'student_id' => 'required|exists:student_profiles,id'
        ]);

        $student = StudentProfile::find($request->student_id);
        $student->teacher_id = $teacher->id;
        $student->save();

        return back()->with('success', 'Alumno asignado correctamente.');
    }
    /**
     * Desasociar alumno del profesor
     */
    public function detachStudent(TeacherProfile $teacher, StudentProfile $student)
    {
        $student->teacher_id = null;
        $student->save();

        return back()->with('success', 'Alumno desasignado correctamente.');
    }

}
