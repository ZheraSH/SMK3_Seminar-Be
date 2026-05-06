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
            ['name' => 'X',   'level_order' => 1],
            ['name' => 'XI',  'level_order' => 2],
            ['name' => 'XII', 'level_order' => 3],
        ];

        foreach ($levels as $level) {
            LevelClass::firstOrCreate(
                [   'name' => $level['name'],
                    'id'          => Str::uuid(),
                    'level_order' => $level['level_order'],
                ]
            );
        }
    }
}