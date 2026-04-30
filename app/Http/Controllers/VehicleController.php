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
        $vehicles = Vehicle::with([
            'teachers' => function ($q) {
                $q->wherePivot('is_primary', true)->with('user');
            }
        ])->get();

        return response()->json([
            'vehicles' => $vehicles
        ]);
    }


    /**
     * MOSTRAR UN VEHÍCULO
     */
    public function show(Vehicle $vehicle)
    {
        return response()->json([
            'vehicle' => $vehicle
        ]);
    }

    /**
     * CREAR VEHÍCULO
     */
    public function store(Request $request)
    {
        $request->validate([
            'plate_number' => 'required|string|max:20|unique:vehicles,plate_number',
            'brand'        => 'required|string|max:50',
            'model'        => 'required|string|max:50',
            'is_active'    => 'nullable|boolean',
            'notes'        => 'required|string',
        ]);

        $vehicle = Vehicle::create([
            'plate_number' => $request->plate_number,
            'brand'        => $request->brand,
            'model'        => $request->model,
            'is_active'    => $request->is_active ? 1 : 0,
            'notes'        => $request->notes,
        ]);
        if ($request->teacher_profile_id) {
            $vehicle->teachers()->sync([
                $request->teacher_profile_id => ['is_primary' => 1]
            ]);
        }

        return response()->json([
            'message' => 'Vehículo creado correctamente',
            'vehicle' => $vehicle
        ]);
    }

    /**
     * ACTUALIZAR VEHÍCULO
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'plate_number' => 'required|string|max:20|unique:vehicles,plate_number,' . $vehicle->id,
            'brand'        => 'required|string|max:50',
            'model'        => 'required|string|max:50',
            'is_active'    => 'nullable|boolean',
            'notes'        => 'required|string',
        ]);

        $vehicle->update([
            'plate_number' => $request->plate_number,
            'brand'        => $request->brand,
            'model'        => $request->model,
            'is_active'    => $request->is_active ? 1 : 0,
            'notes'        => $request->notes,
        ]);
        if ($request->teacher_profile_id) {
            $vehicle->teachers()->sync([
                $request->teacher_profile_id => ['is_primary' => 1]
            ]);
        }
        return response()->json([
            'message' => 'Vehículo actualizado correctamente',
            'vehicle' => $vehicle
        ]);
    }

    /**
     * ELIMINAR VEHÍCULO
     */
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return response()->json([
            'message' => 'Vehículo eliminado correctamente'
        ]);
    }
}
