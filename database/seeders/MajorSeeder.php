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
        $Majors = [
        'PPLG',
        'DKV',
        'DPB',
        'KCS',
        'Kuliner',
        'Perhotelan'
    ];

        foreach ($Majors as $name) {
            Major::firstOrCreate(['name' => $name]);
        }
    }
}
