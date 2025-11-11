<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Classroom;
use App\Models\Major;
use App\Models\LevelClass;
use App\Models\SchoolYear;
use App\Models\Employee; // Pastikan model Employee ada

class ClassroomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $major = Major::where('name', 'PPLG')->first();
        $schoolYear = SchoolYear::latest()->first();

        if (! $major || ! $schoolYear) {
            $this->command->warn('Major PPLG atau SchoolYear belum ada, seeder Classroom dilewati.');
            return;
        }

        // Ambil beberapa guru sebagai wali kelas
        $teachers = Employee::limit(6)->get(); // Ambil 6 guru
        
        if ($teachers->isEmpty()) {
            $this->command->warn('Tidak ada data teacher, seeder Classroom dilewati.');
            return;
        }

        $levels = ['X', 'XI', 'XII'];
        $teacherIndex = 0;

        foreach ($levels as $levelName) {
            $level = LevelClass::where('name', $levelName)->first();

            if (! $level) {
                $this->command->warn("LevelClass {$levelName} belum ada, dilewati.");
                continue;
            }

            for ($i = 1; $i <= 2; $i++) {
                $className = "{$levelName} PPLG {$i}";
                
                // Assign teacher (wali kelas) secara bergiliran
                $teacher = $teachers[$teacherIndex % $teachers->count()];
                $teacherIndex++;

                Classroom::updateOrCreate(
                    [
                        'name' => $className,
                    ],
                    [
                        'id' => Str::uuid(), // Jika menggunakan UUID
                        'slug' => Str::slug($className),
                        'major_id' => $major->id,
                        'level_class_id' => $level->id,
                        'school_year_id' => $schoolYear->id,
                        'teacher_id' => $teacher->id, // Assign wali kelas
                    ]
                );

                $this->command->info("Classroom {$className} created with teacher: {$teacher->user->name}");
            }
        }
    }
}