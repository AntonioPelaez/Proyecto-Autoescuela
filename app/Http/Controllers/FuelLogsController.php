<?php

namespace App\Http\Controllers;

use App\Models\FuelLogs;
use Illuminate\Http\Request;

class FuelLogsController extends Controller
{
    public function index(Request $request){
         $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'from'       => 'nullable|date',
            'to'         => 'nullable|date',
        ]);

        $query = FuelLogs::where('vehicle_id', $request->vehicle_id);

        if ($request->from) {
            $query->whereDate('date', '>=', $request->from);
        }

        if ($request->to) {
            $query->whereDate('date', '<=', $request->to);
        }

        $logs = $query->orderBy('date', 'desc')->get();

        return response()->json([
            'vehicle_id' => $request->vehicle_id,
            'count'      => $logs->count(),
            'fuel_logs'  => $logs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'date'       => 'required|date',
            'liters'     => 'required|numeric|min:0',
            'amount'     => 'required|numeric|min:0',
            'kilometers' => 'nullable|integer|min:0',
            'notes'      => 'nullable|string|max:500',
        ]);

        $log = FuelLogs::create([
            'vehicle_id' => $request->vehicle_id,
            'date'       => $request->date,
            'liters'     => $request->liters,
            'amount'     => $request->amount,
            'kilometers' => $request->kilometers,
            'notes'      => $request->notes,
        ]);

        return response()->json([
            'message'   => 'Repostaje registrado correctamente',
            'fuel_log'  => $log
        ], 201);
    }

    /**
     * Mostrar un repostaje concreto
     */
    public function show($id)
    {
        $log = FuelLogs::findOrFail($id);

        return response()->json($log);
    }

    /**
     * Actualizar un repostaje
     */
    public function update(Request $request, $id)
    {
        $log = FuelLogs::findOrFail($id);

        $request->validate([
            'date'       => 'sometimes|date',
            'liters'     => 'sometimes|numeric|min:0',
            'amount'     => 'sometimes|numeric|min:0',
            'kilometers' => 'nullable|integer|min:0',
            'notes'      => 'nullable|string|max:500',
        ]);

        $log->update($request->all());

        return response()->json([
            'message'  => 'Repostaje actualizado correctamente',
            'fuel_log' => $log
        ]);
    }

    /**
     * Eliminar un repostaje
     */
    public function destroy($id)
    {
        $log = FuelLogs::findOrFail($id);
        $log->delete();

        return response()->json([
            'message' => 'Repostaje eliminado correctamente'
        ]);
    }
}
