<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\ClassroomStudent;
use App\Enums\StudentStatusEnum;
use App\Models\ClassroomStudents;

class ClassroomStudentSeeder extends Seeder
{
    public function run(): void
    {
        $classrooms = Classroom::all();
        $students = Student::all();

        if ($classrooms->isEmpty() || $students->isEmpty()) {
            $this->command->info('No classrooms or students found. Skipping ClassroomStudentSeeder.');
            return;
        }

        $this->command->info("Found {$classrooms->count()} classrooms and {$students->count()} students.");

        $classroomStudentData = [];
        $studentIndex = 0;
        $totalStudents = $students->count();

        $studentsPerClassroom = ceil($totalStudents / $classrooms->count());

        foreach ($classrooms as $classroom) {
            $studentsForThisClassroom = min($studentsPerClassroom, $totalStudents - $studentIndex);
            
            $this->command->info("Assigning {$studentsForThisClassroom} students to {$classroom->name}");

            for ($i = 0; $i < $studentsForThisClassroom; $i++) {
                if ($studentIndex >= $totalStudents) break;

                $student = $students[$studentIndex];
                
                $classroomStudentData[] = [
                    'id' => (string) Str::uuid(),
                    'classroom_id' => $classroom->id,
                    'student_id' => $student->id,
                    'status' => StudentStatusEnum::ACTIVE->value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $studentIndex++;
            }
        }

        ClassroomStudents::insert($classroomStudentData);

        $this->command->info('Successfully assigned ' . count($classroomStudentData) . ' students to ' . $classrooms->count() . ' classrooms.');

        $this->showClassroomSummary();
    }

    private function showClassroomSummary(): void
    {
        $classrooms = Classroom::withCount('students')->get();

        $this->command->info("\nClassroom Student Summary:");
        $this->command->info("==========================");
        
        foreach ($classrooms as $classroom) {
            $this->command->info("{$classroom->name}: {$classroom->students_count} students");
        }
    }
}