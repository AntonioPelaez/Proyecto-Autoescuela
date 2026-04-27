<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ADMIN
        User::create([
            'role_id' => 1,
            'name' => 'Antonio',
            'surname1' => 'García',
            'surname2' => 'López',
            'email' => 'antoniopelaezguerrero8@gmail.com',
            'phone' => '123456789',
            'password' => Hash::make('Anto1234.'),
            'is_active' => true,
            'last_login_at' => now(),
            'remember_token' => null,
        ]);

        // USUARIO NORMAL
        User::create([
            'role_id' => 3, // por ejemplo: 3 = alumno
            'name' => 'Carlos',
            'surname1' => 'Martínez',
            'surname2' => 'Ruiz',
            'email' => 'carlos@example.com',
            'phone' => '600111222',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'last_login_at' => now(),
            'remember_token' => Str::random(60),
        ]);

        // PROFESOR
        User::create([
            'role_id' => 2, // por ejemplo: 2 = profesor
            'name' => 'Manuel',
            'surname1' => 'Gómez',
            'surname2' => 'Santos',
            'email' => 'manuel@example.com',
            'phone' => '600333444',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'last_login_at' => now(),
            'remember_token' => Str::random(60),
        ]);

        User::create([
            'role_id' => 2, // por ejemplo: 2 = profesor
            'name' => 'Trinidad',
            'surname1' => 'Gómez',
            'surname2' => 'Santos',
            'email' => 'trinidad@example.com',
            'phone' => '600345474',
            'password' => Hash::make('password123463.'),
            'is_active' => true,
            'last_login_at' => now(),
            'remember_token' => Str::random(60),
        ]);
    }
}
