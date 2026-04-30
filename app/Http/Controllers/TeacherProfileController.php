<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Town;
use App\Models\TeacherProfile;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeacherProfileController extends Controller
{
    /**
     * LISTADO DE PROFESORES (solo role_id = 2)
     */
    public function index()
    {
        $teachers = TeacherProfile::with('user', 'vehicles')->get()
            ->map(function ($t) {
                return [
                    'id'                    => $t->id,
                    'user_id'               => $t->user_id,
                    'name'             => $t->user->name,
                    'surname1'        =>  $t->user->surname1,
                    "surname2"        => $t->user->surname2,
                    'email'                 => $t->user->email,
                    'dni'                   => $t->dni,
                    'license_number'        => $t->license_number,
                    'notes'                 => $t->notes,
                    'is_active_for_booking' => (bool) $t->is_active_for_booking,
                    'vehicles'              => $t->vehicles,
                ];
            });

        return response()->json($teachers);
    }


    /**
     * LISTA DE USUARIOS DISPONIBLES PARA SER PROFESORES
     */
    public function availableUsers()
    {
        $users = User::where('role_id', 2)
            ->whereDoesntHave('teacherProfile')
            ->get();

        $towns = Town::all();

        return response()->json([
            'users' => $users,
            'towns' => $towns
        ]);
    }

    /**
     * CREAR PROFESOR
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'                 => 'required|string|max:255',
            'surname1'             => 'nullable|string',
            'surname2'             => 'nullable|string',
            'email'                => 'required|email|unique:users,email',
            'active'               => 'nullable|boolean',
            'dni'                  => 'required|string|max:20',
            'license_number'       => 'required|string|max:50',
            'notes'                => 'required|string',
        ]);
        // 2. Crear usuario con contraseña temporal
        $user = User::create([
            'name'       => $request->name,
            'surname1'   => $request->surname1,
            'surname2'   => $request->surname2,
            'email'      => $request->email,
            'password'   => bcrypt('Teachers90.'), // contraseña temporal
            'role_id'    => 2, // profesor
        ]);

        // 3. Crear perfil de profesor
        $teacher = TeacherProfile::create([
            'user_id'              => $user->id,
            'dni'                  => $request->dni,
            'license_number'       => $request->license_number,
            'notes'                => $request->notes,
            'is_active_for_booking' => $request->active ? 1 : 0,
        ]);

        return response()->json([
            'message' => 'Profesor creado correctamente',
            'teacher' => $teacher->load('user'),
        ]);
    }


    /**
     * MOSTRAR PROFESOR
     */
    public function show(TeacherProfile $teacher)
    {
        return response()->json([
            'teacher' => $teacher->load(['user', 'towns', 'vehicles'])
        ]);
    }

    /**
     * ACTUALIZAR PROFESOR
     */
    public function update(Request $request, TeacherProfile $teacher)
    {
        $request->validate([
            'name'                 => 'required|string|max:255',
            'surname1'             => 'nullable|string',
            'surname2'             => 'nullable|string',
            'email'                => 'required|email|unique:users,email,' . $teacher->user_id,
            'dni'                  => 'nullable|string|max:20',
            'license_number'       => 'required|string|max:50',
            'notes'                => 'nullable|string',
            'active'               => 'nullable|boolean',
        ]);

        // 2. Comprobar si el usuario existe
        if (!$teacher->user) {
            return response()->json([
                'message' => 'El usuario asociado a este profesor no existe.',
                'type'    => 'Error'
            ], 422);
        }

        // 3. Actualizar usuario
        $teacher->user->update([
            'name'       => $request->name,
            'surname1'   => $request->surname1,
            'surname2'   => $request->surname2,
            'email'    => $request->email,
        ]);

        // 4. Actualizar perfil de profesor
        $teacher->update([
            'dni'                  => $request->dni,
            'license_number'       => $request->license_number,
            'notes'                => $request->notes,
            'is_active_for_booking' => $request->active ? 1 : 0,
        ]);

        return response()->json([
            'message' => 'Profesor actualizado correctamente',
            'teacher' => $teacher->load('user'),
        ]);
    }




    /**
     * ELIMINAR PROFESOR
     */
    public function destroy(TeacherProfile $teacher)
    {
        $teacher->delete();

        return response()->json([
            'message' => 'Profesor eliminado correctamente'
        ]);
    }

    /**
     * OBTENER NOTAS DEL PROFESOR
     */
    public function notes(TeacherProfile $teacher)
    {
        return response()->json([
            'notes' => $teacher->notes
        ]);
    }

    /**
     * GUARDAR NOTAS DEL PROFESOR
     */
    public function saveNotes(Request $request, TeacherProfile $teacher)
    {
        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $teacher->update([
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'Notas actualizadas correctamente',
            'teacher' => $teacher
        ]);
    }

    /**
     * LISTAR VEHÍCULOS ASIGNADOS Y DISPONIBLES
     */
    public function vehicles(TeacherProfile $teacher)
    {
        $vehicles = Vehicle::all();
        $assigned = $teacher->vehicles()->pluck('vehicle_id')->toArray();

        return response()->json([
            'teacher_id' => $teacher->id,
            'vehicles'   => $vehicles,
            'assigned'   => $assigned
        ]);
    }

    /**
     * ASIGNAR VEHÍCULO
     */
    public function assignVehicle(Request $request, TeacherProfile $teacher)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_at'   => 'nullable|date',
            'end_at'     => 'nullable|date|after_or_equal:start_at',
        ]);

        $teacher->vehicles()->attach($request->vehicle_id, [
            'is_primary' => 0,
            'starts_at'  => $request->start_at,
            'ends_at'    => $request->end_at,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Vehículo asignado correctamente',
            'vehicles' => $teacher->vehicles
        ]);
    }

    /**
     * QUITAR VEHÍCULO
     */
    public function removeVehicle(TeacherProfile $teacher, Vehicle $vehicle)
    {
        $teacher->vehicles()->detach($vehicle->id);

        return response()->json([
            'message' => 'Vehículo desasignado correctamente',
            'vehicles' => $teacher->vehicles
        ]);
    }
    public function toggle(TeacherProfile $teacher)
    {
        $teacher->is_active_for_booking = !$teacher->is_active_for_booking;
        $teacher->save();

        return response()->json([
            'message' => 'Estado actualizado',
            'teacher' => $teacher->load('user', 'vehicles')
        ]);
    }
}
