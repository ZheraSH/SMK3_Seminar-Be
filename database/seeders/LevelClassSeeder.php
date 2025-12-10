<?php

namespace Database\Seeders;

use App\Models\LevelClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LevelClassSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            'X',
            'XI',
            'XII',
        ];

        foreach ($levels as $level) {
            LevelClass::firstOrCreate(
                ['name' => $level],
                ['id' => Str::uuid()]
            );
        }
    }
}