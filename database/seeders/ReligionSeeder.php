<?php

namespace Database\Seeders;

use App\Models\Religion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReligionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $religions = [
            'Islam',
            // 'Kristen',
            // 'Katolik',
            // 'Hindu',
            // 'Budha',
            // 'Konghucu',
        ];

        foreach ($religions as $name) {
            Religion::firstOrCreate(['name' => $name]);
        }

    }
}
