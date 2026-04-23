<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TeacherProfile;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        TeacherProfile::create([
            'user_id' => 2,
            'dni' => '12345678A',
            'license_number' => 'LIC123456',
            'notes' => 'Profesor con amplia experiencia en la enseñanza de conducción.',
            'is_active_for_booking' => true,
        ]);

        TeacherProfile::create([
            'user_id' => 4,
            'dni' => '23458738A',
            'license_number' => 'LIC364665',
            'notes' => 'Profesor con poca experiencia en la enseñanza de conducción.',
            'is_active_for_booking' => false,
        ]);
    }
}
