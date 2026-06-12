<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\VehicleExpenses;
use Illuminate\Http\Request;

class VehicleExpenseController extends Controller
{
    /**
     * Listar todos los gastos del vehículo
     */
    public function index()
    {
        $expenses = VehicleExpenses::orderBy('created_at', 'desc')->get();

        return response()->json([
            'count'    => $expenses->count(),
            'expenses' => $expenses
        ]);
    }

    /**
     * Crear un gasto menor
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id'      => 'required|exists:vehicles,id',
            'amount'          => 'required|numeric|min:0',
            'description'     => 'nullable|string|max:500',
        ]);

        $expense = VehicleExpenses::create([
            'vehicle_id'      => $request->vehicle_id,
            'amount'          => $request->amount,
            'description'     => $request->description,
        ]);

        return response()->json([
            'message' => 'Gasto registrado correctamente',
            'expense' => $expense
        ], 201);
    }

    /**
     * Mostrar un gasto concreto
     */
    public function show($id)
    {
        $expense = VehicleExpenses::findOrFail($id);

        return response()->json($expense);
    }





    /**
     * Actualizar un gasto
     */
    public function update(Request $request, $id)
    {
        $expense = VehicleExpenses::findOrFail($id);

        $request->validate([
            'amount'          => 'sometimes|numeric|min:0',
            'description'     => 'nullable|string|max:500',
        ]);

        $expense->update($request->all());

        return response()->json([
            'message' => 'Gasto actualizado correctamente',
            'expense' => $expense
        ]);
    }

    /**
     * Eliminar un gasto
     */
    public function destroy($id)
    {
        $expense = VehicleExpenses::findOrFail($id);
        $expense->delete();

        return response()->json([
            'message' => 'Gasto eliminado correctamente'
        ]);
    }
}
