<?php

namespace Database\Seeders;

use App\Models\ExamResultStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExamResultStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExamResultStatus::create([
            'name' => 'pendiente',
            'label' => 'Pendiente',
        ]);
        ExamResultStatus::create([
            'name' => 'apto',
            'label' => 'Apto',
        ]);
        ExamResultStatus::create([
            'name' => 'no apto',
            'label' => 'No Apto',
        ]);
        ExamResultStatus::create([
            'name' => 'no presentado',
            'label' => 'No Presentado',
        ]);
    }
}
