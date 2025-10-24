<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolYear;

class SchoolYearSeeder extends Seeder
{
    public function run(): void
    {
        SchoolYear::firstOrCreate(['name' => '2023/2024'], ['active' => false]);
        SchoolYear::firstOrCreate(['name' => '2024/2025'], ['active' => true]);
    }
}
