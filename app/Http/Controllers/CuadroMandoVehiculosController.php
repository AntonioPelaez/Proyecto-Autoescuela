<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\FuelLogs;
use App\Models\VehicleExpenses;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CuadroMandoVehiculosController extends Controller
{
    /**
     * CUADRO DE MANDO PRINCIPAL
     * - Ingresos del vehículo
     * - Gastos del vehículo
     * - Rentabilidad
     * - Indicador rentable/no rentable
     */
    public function resumenGeneral($vehicleId)
    {
        // 1. INGRESOS DEL VEHÍCULO (clases × 25€)
        $classCount = ClassSession::where('vehicle_id', $vehicleId)
            ->where('status', 'completed')
            ->count();

        $income = $classCount * 25; // Precio fijo por clase

        // 2. GASTOS DEL VEHÍCULO
        $fuelExpenses = FuelLogs::where('vehicle_id', $vehicleId)->sum('amount');
        $otherExpenses = VehicleExpenses::where('vehicle_id', $vehicleId)->sum('amount');

        $totalExpenses = $fuelExpenses + $otherExpenses;

        // 3. RENTABILIDAD
        $profit = $income - $totalExpenses;

        return response()->json([
            'vehicle_id'      => $vehicleId,
            'classes_given'   => $classCount,
            'income'          => $income,
            'fuel_expenses'   => $fuelExpenses,
            'other_expenses'  => $otherExpenses,
            'total_expenses'  => $totalExpenses,
            'profit'          => $profit,
            'is_profitable'   => $profit > 0,
            'status'          => $profit > 0 ? 'Rentable' : 'No rentable',
        ]);
    }

    /**
     * COSTE MENSUAL AUTOMÁTICO
     * - Gastos de combustible del mes
     * - Gastos menores del mes
     * - Gasto total del mes
     */
    public function costeMensual(Request $request, $vehicleId)
    {
        $request->validate([
            'month' => 'nullable|date_format:Y-m'
        ]);

        $month = $request->month
            ? Carbon::parse($request->month . '-01')
            : Carbon::now()->startOfMonth();

        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        // Gastos de combustible del mes
        $fuelExpenses = FuelLogs::where('vehicle_id', $vehicleId)
            ->whereBetween('date', [$start, $end])
            ->sum('amount');

        // Gastos menores del mes
        $otherExpenses = VehicleExpenses::where('vehicle_id', $vehicleId)
            ->whereBetween('date', [$start, $end])
            ->sum('amount');

        $totalExpenses = $fuelExpenses + $otherExpenses;

        return response()->json([
            'vehicle_id'     => $vehicleId,
            'month'          => $start->format('Y-m'),
            'fuel_expenses'  => $fuelExpenses,
            'other_expenses' => $otherExpenses,
            'total_expenses' => $totalExpenses,
        ]);
    }

    /**
     * INFORME SIMPLE PARA ADMINISTRACIÓN
     * - Nº clases impartidas
     * - Ingresos totales
     * - Gastos totales
     * - Rentabilidad
     * - Indicador rentable/no rentable
     */
    public function informeAdministracion($vehicleId)
    {
        // 1. Ingresos del vehículo
        $classCount = ClassSession::where('vehicle_id', $vehicleId)
            ->where('status', 'completed')
            ->count();

        $income = $classCount * 25;

        // 2. Gastos del vehículo
        $fuelExpenses = FuelLogs::where('vehicle_id', $vehicleId)->sum('amount');
        $otherExpenses = VehicleExpenses::where('vehicle_id', $vehicleId)->sum('amount');

        $totalExpenses = $fuelExpenses + $otherExpenses;

        // 3. Rentabilidad
        $profit = $income - $totalExpenses;

        return response()->json([
            'vehicle_id'      => $vehicleId,
            'classes_given'   => $classCount,
            'income'          => $income,
            'fuel_expenses'   => $fuelExpenses,
            'other_expenses'  => $otherExpenses,
            'total_expenses'  => $totalExpenses,
            'profit'          => $profit,
            'is_profitable'   => $profit > 0,
            'status'          => $profit > 0 ? 'Rentable' : 'No rentable',
        ]);
    }
    /**
     * Funcionalidad de añadir gasto mensual en un determinado coche 
     */
    public function gastoGasolinaTotalMes(Request $request)
{
    $request->validate([
        'month' => 'nullable|date_format:Y-m'
    ]);

    $month = $request->month
        ? Carbon::parse($request->month . '-01')
        : Carbon::now()->startOfMonth();

    $start = $month->copy()->startOfMonth();
    $end   = $month->copy()->endOfMonth();

    $fuelExpenses = \App\Models\FuelLogs::whereBetween('date', [$start, $end])
        ->sum('amount');

    return response()->json([
        'month'          => $start->format('Y-m'),
        'fuel_expenses'  => $fuelExpenses
    ]);
}


}
