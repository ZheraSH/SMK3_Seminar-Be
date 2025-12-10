<?php

namespace Database\Seeders;

use App\Models\SchoolYear;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SchoolYearSeeder extends Seeder
{
    public function run(): void
    {
        $schoolYears = [
            ['name' => '2023/2024', 'active' => false],
            ['name' => '2024/2025', 'active' => true],
            ['name' => '2025/2026', 'active' => false],
        ];

        foreach ($schoolYears as $schoolYear) {
            SchoolYear::firstOrCreate(
                ['name' => $schoolYear['name']],
                [
                    'id' => Str::uuid(),
                    'active' => $schoolYear['active']
                ]
            );
        }
    }
}