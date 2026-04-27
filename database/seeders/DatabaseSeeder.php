<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            UsersSeeder::class,
            TownsSeeder::class,
            TeacherSeeder::class,
            StudentsSeeder::class,
            TeacherTownsSeeder::class,
            TeacherWeeklyAvailabilitySeeder::class,
            VehicleSeeder::class,
            TeacherVehicleSeeder::class,
        ]);
    }
}
