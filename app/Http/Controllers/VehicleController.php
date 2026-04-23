<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * LISTADO DE VEHÍCULOS
     */
    public function index()
    {
        $vehicles = Vehicle::all();
        return view('vehicles.index', compact('vehicles'));
    }

    /**
     * FORMULARIO DE CREACIÓN
     */
    public function create()
    {
        return view('vehicles.create');
    }

    /**
     * GUARDAR VEHÍCULO
     */
    public function store(Request $request)
    {
        $request->validate([
            'plate_number' => 'required|string|max:20|unique:vehicles,plate_number',
            'brand' => 'required|string|max:50',
            'model' => 'required|string|max:50',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        Vehicle::create([
            'plate_number' => $request->plate_number,
            'brand' => $request->brand,
            'model' => $request->model,
            'is_active' => $request->is_active ? 1 : 0,
            'notes' => $request->notes,
        ]);

        return redirect()->route('vehicles.index')->with('success', 'Vehículo creado correctamente');
    }

    /**
     * FORMULARIO DE EDICIÓN
     */
    public function edit(Vehicle $vehicle)
    {
        return view('vehicles.edit', compact('vehicle'));
    }

    /**
     * ACTUALIZAR VEHÍCULO
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'plate_number' => 'required|string|max:20|unique:vehicles,plate_number,' . $vehicle->id,
            'brand' => 'required|string|max:50',
            'model' => 'required|string|max:50',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $vehicle->update([
            'plate_number' => $request->plate_number,
            'brand' => $request->brand,
            'model' => $request->model,
            'is_active' => $request->is_active ? 1 : 0,
            'notes' => $request->notes,
        ]);

        return redirect()->route('vehicles.index')->with('success', 'Vehículo actualizado correctamente');
    }

    /**
     * ELIMINAR VEHÍCULO
     */
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return redirect()->route('vehicles.index')->with('success', 'Vehículo eliminado correctamente');
    }
}
