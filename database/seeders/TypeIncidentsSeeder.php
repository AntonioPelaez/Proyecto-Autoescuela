<?php

namespace Database\Seeders;

use App\Models\TypeIncidents;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeIncidentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TypeIncidents::insert([
            ['nombre' => 'Enfermedad', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Baja por maternidad', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Baja por paternidad', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Accidente de tráfico','created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Vehículo parado por las autoridades','created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Fallecimiento de un familiar','created_at' => now(), 'updated_at' => now()]
        ]);
    }
}
