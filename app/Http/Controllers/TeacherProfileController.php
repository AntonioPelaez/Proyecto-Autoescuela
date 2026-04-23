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
     * LISTADO DE PROFESORES
     */
    public function index()
    {
        $teachers = TeacherProfile::with(['user', 'towns'])
            ->whereHas('user', function ($q) {
                $q->where('role_id', 2);
            })
            ->get();

        return view('teachers.index', compact('teachers'));
    }

    /**
     * FORMULARIO DE CREACIÓN
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

        $teacher = TeacherProfile::create([
            'user_id' => $request->user_id,
            'dni' => $request->dni,
            'license_number' => $request->license_number,
            'notes' => $request->notes,
            'is_active_for_booking' => $request->is_active_for_booking ? 1 : 0,
        ]);

        if ($request->towns) {
            $teacher->towns()->sync($request->towns);
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

        $teacher->update([
            'dni' => $request->dni,
            'license_number' => $request->license_number,
            'is_active_for_booking' => $request->is_active_for_booking ? 1 : 0,
        ]);

        $teacher->towns()->sync($request->towns ?? []);

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
     * NOTAS
     */
    public function notes(TeacherProfile $teacher)
    {
        return view('teachers.notes', compact('teacher'));
    }

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
     * PÁGINA DE ASIGNACIÓN DE VEHÍCULOS
     */
    public function vehicles(TeacherProfile $teacher)
    {
        $vehicles = Vehicle::all();
        $assigned = $teacher->vehicles()->pluck('vehicle_id')->toArray();

        return view('teachers.vehicles', compact('teacher', 'vehicles', 'assigned'));
    }

    /**
     * ASIGNAR VEHÍCULO
     */
    public function assignVehicle(Request $request, TeacherProfile $teacher)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
        ]);

        $teacher->vehicles()->attach($request->vehicle_id, [
            'is_primary' => 0,
            'starts_at' => $request->start_at,
            'ends_at' => $request->end_at,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Vehículo asignado correctamente.');
    }

    /**
     * QUITAR VEHÍCULO
     */
    public function removeVehicle(TeacherProfile $teacher, Vehicle $vehicle)
    {
        $teacher->vehicles()->detach($vehicle->id);

        return back()->with('success', 'Vehículo desasignado correctamente.');
    }
}
