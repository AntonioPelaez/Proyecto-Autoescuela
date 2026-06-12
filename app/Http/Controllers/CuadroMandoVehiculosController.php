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
public function resumenGeneral(Request $request, $vehicleId)
{
    // Normalizar fechas dd/mm/YYYY → YYYY-MM-DD
    $from = $request->from ? Carbon::createFromFormat('d/m/Y', $request->from)->format('Y-m-d') : null;
    $to   = $request->to   ? Carbon::createFromFormat('d/m/Y', $request->to)->format('Y-m-d') : null;

    // Si hay rango completo → filtrar por periodo
    if ($from && $to) {

        $start = Carbon::parse($from)->startOfDay();
        $end   = Carbon::parse($to)->endOfDay();

        // Rango inválido
        if ($start->gt($end)) {
            return response()->json([
                'vehicle_id'  => $vehicleId,
                'no_data'     => true,
                'period_from' => $request->from,
                'period_to'   => $request->to,
            ]);
        }

        // INGRESOS
        $classCount = ClassSession::where('vehicle_id', $vehicleId)
            ->where('status', 'completed')
            ->whereBetween('session_date', [$start, $end])
            ->count();

        $income = $classCount * 25;

        // GASTOS
        $fuelExpenses = FuelLogs::where('vehicle_id', $vehicleId)
            ->whereBetween('date', [$start, $end])
            ->sum('amount');

        // Opción B: filtrar por created_at
        $otherExpenses = VehicleExpenses::where('vehicle_id', $vehicleId)
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        $totalExpenses = $fuelExpenses + $otherExpenses;

        // NO HAY DATOS EN EL PERIODO
        if ($classCount == 0 && $fuelExpenses == 0 && $otherExpenses == 0) {
            return response()->json([
                'vehicle_id'  => $vehicleId,
                'no_data'     => true,
                'period_from' => $request->from,
                'period_to'   => $request->to,
            ]);
        }

        // SÍ HAY DATOS → devolver solo el periodo
        return response()->json([
            'vehicle_id'      => $vehicleId,
            'period_from'     => $request->from,
            'period_to'       => $request->to,
            'classes_given'   => $classCount,
            'income'          => $income,
            'fuel_expenses'   => $fuelExpenses,
            'other_expenses'  => $otherExpenses,
            'total_expenses'  => $totalExpenses,
            'profit'          => $income - $totalExpenses,
            'is_profitable'   => ($income - $totalExpenses) > 0,
        ]);
    }

    // ACUMULADO (solo si NO hay periodo)
    $firstClassDate = ClassSession::where('vehicle_id', $vehicleId)
        ->where('status', 'completed')
        ->orderBy('session_date', 'asc')
        ->value('session_date');

    if (!$firstClassDate) {
        return response()->json([
            'vehicle_id'      => $vehicleId,
            'classes_given'   => 0,
            'income'          => 0,
            'fuel_expenses'   => 0,
            'other_expenses'  => 0,
            'total_expenses'  => 0,
            'profit'          => 0,
            'is_profitable'   => false,
            'since'           => null,
        ]);
    }

    $start = Carbon::parse($firstClassDate);
    $end = Carbon::now();

    $classCount = ClassSession::where('vehicle_id', $vehicleId)
        ->where('status', 'completed')
        ->whereBetween('session_date', [$start, $end])
        ->count();

    $income = $classCount * 25;

    $fuelExpenses = FuelLogs::where('vehicle_id', $vehicleId)
        ->whereBetween('date', [$start, $end])
        ->sum('amount');

    $otherExpenses = VehicleExpenses::where('vehicle_id', $vehicleId)
        ->whereBetween('created_at', [$start, $end])
        ->sum('amount');

    $totalExpenses = $fuelExpenses + $otherExpenses;

    return response()->json([
        'vehicle_id'      => $vehicleId,
        'classes_given'   => $classCount,
        'income'          => $income,
        'fuel_expenses'   => $fuelExpenses,
        'other_expenses'  => $otherExpenses,
        'total_expenses'  => $totalExpenses,
        'profit'          => $income - $totalExpenses,
        'is_profitable'   => ($income - $totalExpenses) > 0,
        'since'           => $start->format('d/m/Y'),
    ]);
}


/**
 * Funcionalidad que calcula los ingresos mensaules de las clases
 * que se han dado ese mes
 */
public function ingresosMensuales(Request $request, $vehicleId)
{
    $request->validate([
        'month' => 'required|date_format:Y-m'
    ]);

    $month = Carbon::parse($request->month . '-01');
    $start = $month->copy()->startOfMonth();
    $end   = $month->copy()->endOfMonth();

    $classCount = ClassSession::where('vehicle_id', $vehicleId)
        ->where('status', 'completed')
        ->whereBetween('session_date', [$start, $end])
        ->count();

    return response()->json([
        'income' => $classCount * 25
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

    // 🔥 1. GASTO GASOLINA (FuelLogs SÍ tiene columna date)
    $fuelExpenses = FuelLogs::whereBetween('date', [$start, $end])
        ->sum('amount');

    // 🔥 2. GASTO MANTENIMIENTO (VehicleExpenses NO tiene date → usamos created_at)
    $maintenanceExpenses = VehicleExpenses::whereBetween('created_at', [$start, $end])
        ->sum('amount');

    // 🔥 3. TOTAL
    $total = $fuelExpenses + $maintenanceExpenses;

    return response()->json([
        'month'                 => $start->format('Y-m'),
        'fuel_expenses'         => $fuelExpenses,
        'maintenance_expenses'  => $maintenanceExpenses,
        'total_expenses'        => $total
    ]);
}


}
