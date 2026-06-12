<?php

namespace App\Http\Controllers;

use App\Models\FuelLogs;
use Illuminate\Http\Request;

class FuelLogsController extends Controller
{
    public function index(Request $request)
{
    $request->validate([
        'vehicle_id' => 'nullable|exists:vehicles,id',
        'year'       => 'nullable|digits:4',
        'month'      => 'nullable|date_format:Y-m',
        'from'       => 'nullable|date',
        'to'         => 'nullable|date',
    ]);

    $query = FuelLogs::query()->with('vehicle');

    // Filtrar por vehículo
    if ($request->vehicle_id) {
        $query->where('vehicle_id', $request->vehicle_id);
    }

    // ─────────────────────────────────────────────
    // PRIORIDAD DE FILTROS:
    // 1) RANGO (from/to)
    // 2) MES (YYYY-MM)
    // 3) AÑO (YYYY)
    // 4) DÍA (from == to)
    // ─────────────────────────────────────────────

    // 1. FILTRO POR RANGO
    if ($request->from && $request->to) {
        $query->whereBetween('date', [$request->from, $request->to]);
    }
    // 2. FILTRO POR MES
    elseif ($request->month) {
        $year  = substr($request->month, 0, 4);
        $month = substr($request->month, 5, 2);

        $query->whereYear('date', $year)
              ->whereMonth('date', $month);
    }
    // 3. FILTRO POR AÑO
    elseif ($request->year) {
        $query->whereYear('date', $request->year);
    }
    // 4. FILTRO POR DÍA (si from == to)
    elseif ($request->from && !$request->to) {
        $query->whereDate('date', $request->from);
    }

    $logs = $query->orderBy('date', 'desc')->get();

    return response()->json([
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
