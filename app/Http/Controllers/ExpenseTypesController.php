<?php

namespace App\Http\Controllers;

use App\Models\ExpenseTypes;
use Illuminate\Http\Request;

class ExpenseTypesController extends Controller
{
    /**
     * Método dónde se cargan todos los tipos de gastos menores
     * que debe tener un coche.
     */
    public function index(){
        $expense_types = ExpenseTypes::all();
        return response()->json($expense_types);
    }
}
