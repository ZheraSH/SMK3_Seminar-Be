<?php

namespace Database\Seeders;

use App\Models\Classroom;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            ['name' => 'X RPL 1', 'major_id' => 1, 'employee_id' => null, 'school_year' => 1, 'level_class_id' => 1],
            ['name' => 'X RPL 2', 'major_id' => 1, 'employee_id' => null, 'school_year' => 1, 'level_class_id' => 1],
            ['name' => 'XI RPL 1', 'major_id' => 1, 'employee_id' => null, 'school_year' => 1, 'level_class_id' => 2],
            ['name' => 'XII RPL 1', 'major_id' => 1, 'employee_id' => null, 'school_year' => 1, 'level_class_id' => 3],
        ];

        foreach ($classes as $c) {
            Classroom::firstOrCreate(
                ['slug' => Str::slug($c['name'])],
                [
                    'name' => $c['name'],
                    'major_id' => $c['major_id'],
                    'employee_id' => $c['employee_id'],
                    'school_year' => $c['school_year'],
                    'level_class_id' => $c['level_class_id'],
                ]
            );
        }
    }
}


