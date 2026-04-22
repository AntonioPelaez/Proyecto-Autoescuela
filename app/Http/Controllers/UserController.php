<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Muestra el listado de usuarios con búsqueda.
     */
    public function index(Request $request)
    {
        // Capturamos el texto de búsqueda
        $search = $request->input('search');

        // Consulta con filtro por nombre o email
        $users = User::when($search, function ($query, $search) {
            return $query->where('name', 'LIKE', "%$search%")
                         ->orWhere('email', 'LIKE', "%$search%");
        })
        ->orderBy('id', 'DESC')
        ->paginate(10);

        // Retornamos la vista con los datos
        return view('users.index', compact('users', 'search'));
    }

    /**
     * Muestra el formulario para crear un usuario.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Guarda un nuevo usuario en la base de datos.
     */
    public function store(Request $request)
    {

        // Validación de datos
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6'
        ]);

        // Crear usuario
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            // Encriptamos la contraseña
            'password' => bcrypt($request->password),
            'email_verified_at' => $request->email_verified_at,
            'remember_token' => $request->remember_token
        ]);

        // Redirigir al listado
        return redirect()->route('users.index')
                         ->with('success', 'Usuario creado correctamente');
    }

    /**
     * Muestra el formulario de edición.
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Actualiza un usuario existente.
     */
    public function update(Request $request, User $user)
    {
        // Validación
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,$user->id",
            'password' => 'nullable|min:6'
        ]);

        // Actualización de datos
        $user->name = $request->name;
        $user->email = $request->email;
        $user->email_verified_at = $request->email_verified_at;
        $user->remember_token = $request->remember_token;

        // Solo actualizamos contraseña si se envía
        if ($request->password) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return redirect()->route('users.index')
                         ->with('success', 'Usuario actualizado correctamente');
    }

    /**
     * Elimina un usuario.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')
                         ->with('success', 'Usuario eliminado correctamente');
    }
}
