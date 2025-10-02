<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SchoolYear;

class SchoolYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $year = [
            [
                'school_year' => '2021/2022',
                'active' => '0',
                'created_at' => now()->subDays(4),
            ],
            [
                'school_year' => '2022/2023',
                'active' => '0',
                'created_at' => now()->subDays(3),
            ],
            [
                'school_year' => '2023/2024',
                'active' => '0',
                'created_at' => now()->subDays(2),
            ],
            [
                'school_year' => '2024/2025',
                'active' => '1',
                'created_at' => now()->subDays(1),
            ]
        ];

        foreach ($year as $item => $data) {
            SchoolYear::insert([
                'school_year' => $data['school_year'],
                'active' => $data['active'],
                'created_at'=> $data['created_at']
            ]);
        };
    }
}
