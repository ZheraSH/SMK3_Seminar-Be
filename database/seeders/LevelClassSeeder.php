<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LevelClass;

class LevelClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levelClasses = ['X', 'XI', 'XII', 'Alumni'];

        foreach ($levelClasses as $name) {
            LevelClass::firstOrCreate(['name' => $name]);
        }
    }
}
