<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Major;
use App\Models\LevelClass;
use App\Models\SchoolYear;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClassroomSeeder extends Seeder
{
    private const CLASSES_PER_LEVEL = 3;
    private const TOTAL_CLASSES = 9;

    public function run(): void
    {
        $major = Major::where('code', 'PPLG')->first();
        $schoolYear = SchoolYear::where('active', true)->first();
        $levels = LevelClass::all();
        
        $homeroomTeachers = Employee::whereHas('user.roles', function($q) {
            $q->where('name', 'homeroom_teacher');
        })->get();

        if ($homeroomTeachers->count() < self::TOTAL_CLASSES) {
            return;
        }

        $teacherIndex = 0;
        $usedTeachers = [];

        foreach ($levels as $level) {
            for ($i = 1; $i <= self::CLASSES_PER_LEVEL; $i++) {
                $className = "{$level->name} PPLG {$i}";
                
                $teacher = $homeroomTeachers[$teacherIndex];
                $teacherIndex++;

                if (in_array($teacher->id, $usedTeachers)) {
                    continue;
                }

                Classroom::updateOrCreate(
                    ['name' => $className],
                    [
                        'id' => Str::uuid(),
                        'slug' => Str::slug($className),
                        'major_id' => $major->id,
                        'level_class_id' => $level->id,
                        'school_year_id' => $schoolYear->id,
                        'homeroom_teacher_id' => $teacher->id,
                    ]
                );
                
                $usedTeachers[] = $teacher->id;
            }
        }
    }
}