<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * LISTADO DE USUARIOS + BÚSQUEDA
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::with('role')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('surname1', 'LIKE', "%{$search}%")
                      ->orWhere('surname2', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy('id', 'DESC')
            ->paginate(10);

        return response()->json([
            'search' => $search,
            'users'  => $users
        ]);
    }

    /**
     * LISTA DE ROLES DISPONIBLES
     */
    public function roles()
    {
        return response()->json([
            'roles' => Role::all()
        ]);
    }

    /**
     * CREAR USUARIO
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required',
            'surname1'  => 'nullable',
            'surname2'  => 'nullable',
            'email'     => 'required|email|unique:users,email',
            'phone'     => 'nullable',
            'role_id'   => 'nullable|exists:roles,id',
            'password'  => 'required|min:6',
            'is_active' => 'nullable|boolean',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'surname1'  => $request->surname1,
            'surname2'  => $request->surname2,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'role_id'   => $request->role_id,
            'password'  => Hash::make($request->password),
            'is_active' => $request->is_active ? 1 : 0,
        ]);

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'user'    => $user
        ]);
    }

    /**
     * MOSTRAR USUARIO
     */
    public function show(User $user)
    {
        return response()->json([
            'user' => $user->load('role')
        ]);
    }

    /**
     * ACTUALIZAR USUARIO
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'      => 'required',
            'surname1'  => 'nullable',
            'surname2'  => 'nullable',
            'email'     => "required|email|unique:users,email,{$user->id}",
            'phone'     => 'nullable',
            'role_id'   => 'nullable|exists:roles,id',
            'is_active' => 'nullable|boolean',
            'password'  => 'nullable|min:6',
        ]);

        // Actualizar datos básicos
        $user->update([
            'name'      => $request->name,
            'surname1'  => $request->surname1,
            'surname2'  => $request->surname2,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'role_id'   => $request->role_id,
            'is_active' => $request->is_active ? 1 : 0,
        ]);

        // Actualizar contraseña si se envía
        if ($request->password) {
            $user->update([
                'password' => Hash::make($request->password)
            ]);
        }

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'user'    => $user
        ]);
    }

    /**
     * ELIMINAR USUARIO
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado correctamente'
        ]);
    }
}
