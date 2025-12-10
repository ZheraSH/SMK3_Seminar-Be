<?php

namespace Database\Seeders;

use App\Models\Religion;
use Illuminate\Database\Seeder;

class ReligionSeeder extends Seeder
{
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
