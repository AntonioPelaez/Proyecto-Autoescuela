<?php

namespace Database\Seeders;

use App\Models\TeacherVehicle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeacherVehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = new TeacherVehicle();
        $vehicles->teacher_profile_id = 1;
        $vehicles->vehicle_id = 1;
        $vehicles->starts_at = now()->subMonths(6);
        $vehicles->ends_at = null;
        $vehicles->is_primary = true;
        $vehicles->save();
        $vehicles2 = new TeacherVehicle();
        $vehicles2->teacher_profile_id = 2;
        $vehicles2->vehicle_id = 2;
        $vehicles2->starts_at = now()->subMonths(3);
        $vehicles2->ends_at = null;
        $vehicles2->is_primary = true;
        $vehicles2->save();
    }
}
