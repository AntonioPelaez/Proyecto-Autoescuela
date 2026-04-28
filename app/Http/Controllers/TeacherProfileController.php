<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Town;
use App\Models\TeacherProfile;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class TeacherProfileController extends Controller
{
    /**
     * LISTADO DE PROFESORES (solo role_id = 2)
     */
    public function index()
    {
        $teachers = TeacherProfile::with(['user', 'towns'])
            ->whereHas('user', function ($q) {
                $q->where('role_id', 2);
            })
            ->get();

        return response()->json([
            'teachers' => $teachers
        ]);
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
            'user_id'              => 'required|exists:users,id',
            'dni'                  => 'nullable|string|max:20',
            'license_number'       => 'required|string|max:50',
            'notes'                => 'nullable|string',
            'is_active_for_booking'=> 'nullable|boolean',
            'towns'                => 'nullable|array',
        ]);

        $teacher = TeacherProfile::create([
            'user_id'              => $request->user_id,
            'dni'                  => $request->dni,
            'license_number'       => $request->license_number,
            'notes'                => $request->notes,
            'is_active_for_booking'=> $request->is_active_for_booking ? 1 : 0,
        ]);

        // Asignar pueblos automáticamente
        if ($request->towns) {
            $teacher->towns()->sync($request->towns);
        }

        return response()->json([
            'message' => 'Profesor creado correctamente',
            'teacher' => $teacher->load('towns')
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
            'dni'                  => 'nullable|string|max:20',
            'license_number'       => 'required|string|max:50',
            'is_active_for_booking'=> 'nullable|boolean',
            'towns'                => 'nullable|array',
        ]);

        $teacher->update([
            'dni'                  => $request->dni,
            'license_number'       => $request->license_number,
            'is_active_for_booking'=> $request->is_active_for_booking ? 1 : 0,
        ]);

        // Actualizar pueblos
        $teacher->towns()->sync($request->towns ?? []);

        return response()->json([
            'message' => 'Profesor actualizado correctamente',
            'teacher' => $teacher->load('towns')
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
}
