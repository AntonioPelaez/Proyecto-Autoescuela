<?php

namespace Database\Seeders;

use App\Models\ExpenseTypes;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExpenseTypes::create([
            'name' => 'Lavado de coches',
        ]);
        ExpenseTypes::create([
            'name' => 'Reparación del chasis',
        ]);
        ExpenseTypes::create([
            'name' => 'Sustitución de la bateria',
        ]);
        ExpenseTypes::create([
            'name' => 'Arreglo de la correa de distribución',
        ]);
        ExpenseTypes::create([
            'name' => 'Mantenimiento de critales',
        ]);
        ExpenseTypes::create([
            'name' => 'Mantenimiento de las luces',
        ]);
        ExpenseTypes::create([
            'name' => 'Arreglo del panel de instrumentos',
        ]);
        ExpenseTypes::create([
            'name' => 'Mantenimiento de asientos y cinturones de seguridad',
        ]);
        ExpenseTypes::create([
            'name' => 'Sustitución de ruedas',
        ]);
        ExpenseTypes::create([
            'name' => 'Manteniemiento de la suspensión del coche',
        ]);
        ExpenseTypes::create([
            'name' => 'Mantenimiento de los frenos del coche',
        ]);
        ExpenseTypes::create([
            'name' => 'Mantenimiento de los pedales del coche',
        ]);
        ExpenseTypes::create([
            'name' => 'Mantenimiento del embrague y acelerador del coche',
        ]);
        ExpenseTypes::create([
            'name' => 'Mantenimiento del motor del coche',
        ]);
        ExpenseTypes::create([
            'name' => 'Revisión de líquidos (limpiaparabrisas y refrigerante)',
        ]);
        ExpenseTypes::create([
            'name' => 'Sustitución del aceite del coche',
        ]);
    }
}
