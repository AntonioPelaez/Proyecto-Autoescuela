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

    public function history($vehicleId)
{
    // Repostajes
    $fuelLogs = \App\Models\FuelLogs::where('vehicle_id', $vehicleId)
        ->orderBy('date', 'desc')
        ->get();

    // Gastos menores
    $expenses = \App\Models\VehicleExpenses::where('vehicle_id', $vehicleId)
        ->orderBy('date', 'desc')
        ->get();

    // Km por clases
    $classKm = \App\Models\ClassSession::where('vehicle_id', $vehicleId)
        ->whereNotNull('start_km')
        ->whereNotNull('end_km')
        ->orderBy('session_date', 'desc')
        ->get()
        ->map(function ($s) {
            return [
                'type'        => 'class',
                'date'        => $s->session_date,
                'start_km'    => $s->start_km,
                'end_km'      => $s->end_km,
                'km_done'     => max(0, $s->end_km - $s->start_km),
                'student'     => $s->studentProfile->user->name ?? null,
                'teacher'     => $s->teacherProfile->user->name ?? null,
            ];
        });

    // Km por exámenes
    $examKm = \App\Models\ExamStudents::where('vehicle_id', $vehicleId)
        ->whereNotNull('start_km')
        ->whereNotNull('end_km')
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($e) {
            return [
                'type'        => 'exam',
                'date'        => $e->examCall->exam_date ?? null,
                'start_km'    => $e->start_km,
                'end_km'      => $e->end_km,
                'km_done'     => max(0, $e->end_km - $e->start_km),
                'student'     => $e->student->user->name ?? null,
                'teacher'     => $e->examCall->teacher->user->name ?? null,
            ];
        });

    // Unimos km de clases y exámenes
    $kmHistory = $classKm->merge($examKm)->sortByDesc('date')->values();

    return response()->json([
        'vehicle_id' => $vehicleId,
        'fuel_logs'  => $fuelLogs,
        'expenses'   => $expenses,
        'km_history' => $kmHistory,
    ]);
}

}
