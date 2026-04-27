<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehiculos = new \App\Models\Vehicle();
        $vehiculos->plate_number = '1234ABC';
        $vehiculos->brand = 'Toyota';
        $vehiculos->model = 'Corolla';
        $vehiculos->is_active = true;
        $vehiculos->notes = 'Vehículo de prácticas para el profesor Manuel Gómez Santos.';
        $vehiculos->save();
        $vehiculos2 = new \App\Models\Vehicle();
        $vehiculos2->plate_number = '5678DEF';
        $vehiculos2->brand = 'Honda';
        $vehiculos2->model = 'Civic';
        $vehiculos2->is_active = true;
        $vehiculos2->notes = 'Vehículo de prácticas para el profesor Manuel Gómez Santos.';
        $vehiculos2->save();

    }
}
