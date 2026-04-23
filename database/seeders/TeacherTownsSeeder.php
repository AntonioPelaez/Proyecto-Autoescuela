<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeacherTownsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teacherTown = new \App\Models\TeacherTown();
        $teacherTown->teacher_profile_id = 1;
        $teacherTown->town_id = 1;
        $teacherTown->save();

        $teacherTown1 = new \App\Models\TeacherTown();
        $teacherTown1->teacher_profile_id = 2;
        $teacherTown1->town_id = 2;
        $teacherTown1->save();
    }
}
