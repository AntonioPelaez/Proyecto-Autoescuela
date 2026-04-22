<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role; // Importamos el modelo Role
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * LISTADO DE USUARIOS
     * - Carga los roles con with('role')
     * - Permite buscar por nombre, apellidos o email
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::with('role') // Cargamos el rol asociado
            ->when($search, function ($query, $search) {
                return $query->where('name', 'LIKE', "%{$search}%")
                             ->orWhere('surname1', 'LIKE', "%{$search}%")
                             ->orWhere('surname2', 'LIKE', "%{$search}%")
                             ->orWhere('email', 'LIKE', "%{$search}%");
            })
            ->orderBy('id', 'DESC')
            ->paginate(10);

        return view('users.index', compact('users', 'search'));
    }

    /**
     * FORMULARIO DE CREACIÓN
     * - Cargamos los roles para el select
     */
    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    /**
     * GUARDAR USUARIO
     * - Valida datos
     * - Crea usuario
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'surname1' => 'nullable',
            'surname2' => 'nullable',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable',
            'role_id' => 'nullable|exists:roles,id',
            'password' => 'required|min:6',
            'is_active' => 'nullable|boolean',
        ]);

        User::create([
            'name' => $request->name,
            'surname1' => $request->surname1,
            'surname2' => $request->surname2,
            'email' => $request->email,
            'phone' => $request->phone,
            'role_id' => $request->role_id,
            'password' => $request->password, // Laravel lo hashea automáticamente
            'is_active' => $request->is_active ? 1 : 0,
        ]);

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente');
    }

    /**
     * FORMULARIO DE EDICIÓN
     * - Cargamos roles para el select
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * ACTUALIZAR USUARIO
     * - Si se envía contraseña nueva, se actualiza
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required',
            'surname1' => 'nullable',
            'surname2' => 'nullable',
            'email' => "required|email|unique:users,email,{$user->id}",
            'phone' => 'nullable',
            'role_id' => 'nullable|exists:roles,id',
            'is_active' => 'nullable|boolean',
            'password' => 'nullable|min:6',
        ]);

        // Actualizamos datos básicos
        $user->update([
            'name' => $request->name,
            'surname1' => $request->surname1,
            'surname2' => $request->surname2,
            'email' => $request->email,
            'phone' => $request->phone,
            'role_id' => $request->role_id,
            'is_active' => $request->is_active ? 1 : 0,
        ]);

        // Si se envía nueva contraseña → actualizarla
        if ($request->password) {
            $user->update([
                'password' => $request->password
            ]);
        }

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente');
    }

    /**
     * ELIMINAR USUARIO
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente');
    }
}
