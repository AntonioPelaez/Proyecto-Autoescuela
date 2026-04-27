<?php

namespace Database\Seeders;

use App\Models\TeacherWeeklyAvailability;
use Illuminate\Database\Seeder;

class TeacherWeeklyAvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        TeacherWeeklyAvailability::create([
            'teacher_profile_id' => 2,
            'town_id' => 2,
            'day_of_week' => 1,
            'starts_time' => '08:00:00',
            'end_time' => '11:00:00',
            'slot_minutes' => 60,
            'is_active' => 1,
        ]);

        TeacherWeeklyAvailability::create([
            'teacher_profile_id' => 2,
            'town_id' => 2,
            'day_of_week' => 2,
            'starts_time' => '10:00:00',
            'end_time' => '14:00:00',
            'slot_minutes' => 60,
            'is_active' => 1,
        ]);

        TeacherWeeklyAvailability::create([
            'teacher_profile_id' => 2,
            'town_id' => 2,
            'day_of_week' => 3,
            'starts_time' => '15:00:00',
            'end_time' => '19:00:00',
            'slot_minutes' => 60,
            'is_active' => 1,
        ]);

        TeacherWeeklyAvailability::create([
            'teacher_profile_id' => 2,
            'town_id' => 2,
            'day_of_week' => 4,
            'starts_time' => '09:00:00',
            'end_time' => '12:00:00',
            'slot_minutes' => 60,
            'is_active' => 1,
        ]);

        TeacherWeeklyAvailability::create([
            'teacher_profile_id' => 2,
            'town_id' => 2,
            'day_of_week' => 5,
            'starts_time' => '16:00:00',
            'end_time' => '20:00:00',
            'slot_minutes' => 60,
            'is_active' => 1,
        ]);
    }
}
