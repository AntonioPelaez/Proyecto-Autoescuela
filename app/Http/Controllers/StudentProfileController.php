<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            // Datos del alumno
            'dni'          => 'nullable|string|max:20',
            'birth_date'   => 'nullable|date',
            'pickup_notes' => 'nullable|string|max:255',

            // Datos del usuario
            'name'      => 'nullable|string|max:255',
            'surname1'  => 'nullable|string|max:255',
            'surname2'  => 'nullable|string|max:255',
            'email'     => "nullable|email|unique:users,email,{$student->user_id}",
            'phone'     => 'nullable|string|max:50',
        ]);

        // 1. Actualizar perfil del alumno
        $student->update([
            'dni'          => $request->dni,
            'birth_date'   => $request->birth_date,
            'pickup_notes' => $request->pickup_notes,
        ]);
        // 2. Actualizar datos del usuario asociado
        if ($student->user) {
            $student->user->update([
                'name'      => $request->name,
                'surname1'  => $request->surname1,
                'surname2'  => $request->surname2,
                'email'     => $request->email,
                'phone'     => $request->phone,
            ]);
        } else {
            return response()->json([
                'message' => 'El alumno no tiene un usuario asociado.',
            ], 422);
        }


        // 3. Recargar relaciones
        $student->load('user');

        return response()->json([
            'message' => 'Alumno actualizado correctamente',
            'student' => [
                'id'            => $student->id,
                'user_id'       => $student->user_id,
                'dni'           => $student->dni,
                'birth_date'    => $student->birth_date,
                'pickup_notes'  => $student->pickup_notes,

                // Datos del usuario
                'name'          => $student->user->name,
                'surname1'      => $student->user->surname1,
                'surname2'      => $student->user->surname2,
                'email'         => $student->user->email,
                'phone'         => $student->user->phone,
            ]
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
    /**
     * Cambiar contraseña para el estudiante
     */
public function changePassword(Request $request, StudentProfile $student)
{
    $request->validate([
        'current_password' => 'required|string',
        'password'         => 'required|string|min:8|confirmed',
    ]);

    // Verificar que el alumno tiene usuario asociado
    if (!$student->user) {
        return response()->json([
            'message' => 'El alumno no tiene un usuario asociado.'
        ], 422);
    }

    $user = $student->user;

    // Verificar contraseña actual
    if (!Hash::check($request->current_password, $user->password)) {
        return response()->json([
            'message' => 'La contraseña actual no es correcta.'
        ], 422);
    }

    // Actualizar contraseña
    $user->update([
        'password' => Hash::make($request->password)
    ]);

    return response()->json([
        'message' => 'Contraseña actualizada correctamente.'
    ]);
}

}
