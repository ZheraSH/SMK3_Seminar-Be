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
    // Konfigurasi jumlah kelas per level - MUDAH DIUBAH!
    private const CLASSES_PER_LEVEL = 3;

    public function run(): void
    {
        $major = Major::where('code', 'PPLG')->first();
        $schoolYear = SchoolYear::where('active', true)->first();
        $levels = LevelClass::all();
        $teachers = Employee::whereHas('user.roles', function($q) {
            $q->whereIn('name', ['teacher', 'homeroom_teacher']);
        })->get();

        if (!$major || !$schoolYear || $levels->isEmpty() || $teachers->isEmpty()) {
            $this->command->error('Required data not found for ClassroomSeeder');
            return;
        }

        $teacherIndex = 0;

        foreach ($levels as $level) {
            for ($i = 1; $i <= self::CLASSES_PER_LEVEL; $i++) {
                $className = "{$level->name} PPLG {$i}";
                
                // Get teacher (rotate through available teachers)
                $teacher = $teachers[$teacherIndex % $teachers->count()];
                $teacherIndex++;

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
            }
        }
        
        $this->command->info("Created " . ($levels->count() * self::CLASSES_PER_LEVEL) . " classrooms");
    }
}