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
        SchoolYear::firstOrCreate(['school_year' => '2023/2024'], ['active' => false]);
        SchoolYear::firstOrCreate(['school_year' => '2024/2025'], ['active' => true]);
    }
}
