<?php

namespace Database\Seeders;

use App\Models\Religion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReligionSeeder extends Seeder
{
    public function run(): void
    {
        $religions = [
            'Islam',
            'Kristen',
            'Katolik',
            'Hindu',
            'Budha',
            'Konghucu',
        ];

        foreach ($religions as $religion) {
            Religion::firstOrCreate(
                ['name' => $religion],
                ['id' => Str::uuid()]
            );
        }
    }
}
