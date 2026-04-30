<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registrar un nuevo usuario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|min:6',
            'name'          => 'required|string|max:80',
            'surname1'      => 'nullable|string|max:80',
            'surname2'      => 'nullable|string|max:80',
            'phone'         => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'dni'           => 'nullable|string|max:20',
            'pickup_notes'  => 'nullable|string|max:255',
            'role_id'       => 'required|in:1,2,3', // 1 admin, 2 profesor, 3 alumno
            'license_number' => 'nullable|string|max:50',
            'notes'         => 'nullable|string',
        ]);

        // Crear usuario
        $user = User::create([
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'name'      => $validated['name'],
            'surname1'  => $validated['surname1'],
            'surname2'  => $validated['surname2'],
            'phone'     => $validated['phone'],
            'role_id'   => $validated['role_id'],
        ]);

        // Crear perfil según el rol
        if ($validated['role_id'] == 3) {
            // Alumno
            StudentProfile::create([
                'user_id'      => $user->id,
                'dni'          => $validated['dni'] ?? null,
                'birth_date'   => $validated['date_of_birth'] ?? null,
                'pickup_notes' => $validated['pickup_notes'] ?? null,
            ]);
        }

        if ($validated['role_id'] == 2) {
            // Profesor
            TeacherProfile::create([
                'user_id'              => $user->id,
                'dni'                  => $validated['dni'] ?? null,
                'license_number'       => $validated['license_number'] ?? null,
                'notes'                => $validated['notes'] ?? null,
                'is_active_for_booking' => true,
            ]);
        }
        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'user'    => $user->load(['studentProfile', 'teacherProfile']),
        ], 201);
    }

    /**
     * Iniciar sesión de un usuario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Credenciales incorrectas',
            ]);
        }
        $user->update([
            'last_login_at' => now(),
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login correcto',
            'token' => $token,
            'user' => $user,
            'role' => $user->role->name,
        ]);
    }
    /**
     * Cerrar sesión del usuario autenticado.
     * Este método elimina el token de acceso actual, invalidando la sesión.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout correcto',
        ]);
    }
    /**
     * Obtener información del usuario autenticado.
     */
    public function me(Request $request)
    {
        $user = $request->user()->load([
        'studentProfile',
        'teacherProfile',
    ]);
        return response()->json($user);
    }
}
