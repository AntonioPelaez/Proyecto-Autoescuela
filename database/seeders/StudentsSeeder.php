<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $student = new \App\Models\StudentProfile();
        $student->user_id = 2;
        $student->dni = '12345678A';
        $student->birth_date = '2000-01-01';
        $student->pickup_notes = 'El alumno prefiere ser recogido en la puerta principal.';
        $student->save();

    }
}
