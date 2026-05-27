<?php

namespace Database\Seeders;

use App\Models\ExamCallStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExamCallStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExamCallStatus::create([
            'name' => 'programada',
            'label' => 'Programada',
        ]);
        ExamCallStatus::create([
            'name' => 'finalizada',
            'label' => 'Finalizada',
        ]);
        ExamCallStatus::create([
            'name' => 'cancelada',
            'label' => 'Cancelada',
        ]);
    }
}
