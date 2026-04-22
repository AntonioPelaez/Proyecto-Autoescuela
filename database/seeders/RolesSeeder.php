<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $roles = new Role();
       $roles->code = 'admin';
       $roles->name = 'admin';
       $roles->save();
       $roles1 = new Role();
       $roles1->code = 'teacher';
       $roles1->name = 'teacher';
       $roles1->save();
       $roles2 = new Role();
       $roles2->code = 'student';
       $roles2->name = 'student';
       $roles2->save();
    }
}
