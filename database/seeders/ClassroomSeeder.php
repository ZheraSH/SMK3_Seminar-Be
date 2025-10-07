<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Major;
use App\Models\LevelClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $majors = Major::all();
        $levels = LevelClass::all();

        foreach ($majors as $major) {
            foreach ($levels as $level) {
                $name = "{$major->name} - {$level->name}";
                $slug = Str::slug($name);

                Classroom::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $name,
                        'major_id' => $major->id,
                        'level_class_id' => $level->id,
                        'employee_id' => null,
                        'school_year' => 1,
                    ]
                );
            }
        }
    }
}
