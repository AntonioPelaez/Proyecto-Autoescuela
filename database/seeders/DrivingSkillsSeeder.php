<?php

namespace Database\Seeders;

use App\Models\DrivingSkills;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DrivingSkillsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DrivingSkills::create(['name' => 'Manejo del vehículo']);
        DrivingSkills::create(['name' => 'Circulación urbana']);
        DrivingSkills::create(['name' => 'Aparcamiento']);
        DrivingSkills::create(['name' => 'Incorporaciones']);
        DrivingSkills::create(['name' => 'Rotondas']);
        DrivingSkills::create(['name' => 'Señales']);
        DrivingSkills::create(['name' => 'Seguridad']);
        DrivingSkills::create(['name' => 'Autonomía']);
    }
}
