<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TownsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $towns = new \App\Models\Town();
        $towns->name = 'Archidona';
        $towns->postal_code = '29300';
        $towns->save();
        $towns1 = new \App\Models\Town();
        $towns1->name = 'Antequera';
        $towns1->postal_code = '29200';
        $towns1->save();
    }
}
