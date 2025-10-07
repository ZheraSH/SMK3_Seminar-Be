<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Major;

class MajorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $Majors = ['RPL', 'DKV', 'DPB', 'PH', 'KCS', 'Kuliner'];

        foreach ($Majors as $name) {
            Major::firstOrCreate(['name' => $name]);
        }
    }
}
