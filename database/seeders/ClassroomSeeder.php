<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\{Classroom, Major, LevelClass, SchoolYear, Employee};

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $major = Major::where('code', 'PPLG')->first();
        $schoolYear = SchoolYear::latest()->first();
        $teachers = Employee::limit(6)->get();
        $levels = ['X', 'XI', 'XII'];
        $teacherIndex = 0;

        foreach ($levels as $levelName) {
            $level = LevelClass::where('name', $levelName)->first();
            if (! $level) continue;

            for ($i = 1; $i <= 3; $i++) {
                $className = "{$levelName} PPLG {$i}";
                $teacher = $teachers[$teacherIndex++ % $teachers->count()];

                Classroom::updateOrCreate(
                    ['name' => $className],
                    [
                        'id' => Str::uuid(),
                        'slug' => Str::slug($className),
                        'major_id' => $major->id,
                        'level_class_id' => $level->id,
                        'school_year_id' => $schoolYear->id,
                        'teacher_id' => $teacher->id,
                    ]
                );
            }
        }
    }
}
