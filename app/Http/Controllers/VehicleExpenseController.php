<?php

namespace App\Http\Controllers;

use App\Models\VehicleExpenses;
use Illuminate\Http\Request;

class VehicleExpenseController extends Controller
{
    public function index(Request $request)
{
    $request->validate([
        'vehicle_id'       => 'required|exists:vehicles,id',
        'expense_type_id'  => 'nullable|exists:expense_types,id',
        'expense_type'     => 'nullable|string',
    ]);

    $query = VehicleExpenses::where('vehicle_id', $request->vehicle_id);

    // Filtrar por ID de tipo de gasto
    if ($request->expense_type_id) {
        $query->where('expense_type_id', $request->expense_type_id);
    }

    // Filtrar por nombre del tipo de gasto
    if ($request->expense_type) {
        $query->whereHas('expenseType', function ($q) use ($request) {
            $q->where('name', 'LIKE', '%' . $request->expense_type . '%');
        });
    }

    // 🔥 Ya NO hay filtros de fecha

    $expenses = $query->orderBy('date', 'desc')->get();

    return response()->json([
        'vehicle_id' => $request->vehicle_id,
        'count'      => $expenses->count(),
        'expenses'   => $expenses
    ]);
}

    /**
     * Crear un gasto menor
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id'      => 'required|exists:vehicles,id',
            'expense_type_id' => 'nullable|exists:expense_types,id',
            'class_session_id' => 'required|exists:class_sessions,id',
            'amount'          => 'required|numeric|min:0',
            'description'     => 'nullable|string|max:500',
        ]);

        $expense = VehicleExpenses::create([
            'vehicle_id'      => $request->vehicle_id,
            'class_session_id'=> $request->class_session_id,
            'expense_type_id' => $request->expense_type_id,
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
            'expense_type_id' => 'nullable|exists:expense_types,id',
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
