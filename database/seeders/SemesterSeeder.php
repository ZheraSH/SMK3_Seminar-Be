<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Semester;

class SemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $semesters = [
            ['name' => 'Ganjil', 'active' => false],
            ['name' => 'Genap', 'active' => false],
        ];

        foreach ($semesters as $data) {
            Semester::firstOrCreate(['name' =>$data['name']],$data);
        }
    }
}
