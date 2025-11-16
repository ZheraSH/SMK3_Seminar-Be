<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\ClassroomStudents;
use App\Enums\StudentStatusEnum;

class ClassroomStudentSeeder extends Seeder
{
    public function run(): void
    {
        $classrooms = Classroom::all();
        $students = Student::all();

        if ($classrooms->isEmpty() || $students->isEmpty()) {
            return;
        }

        $classroomStudentData = [];
        $studentIndex = 0;
        $totalStudents = $students->count();
        $studentsPerClassroom = ceil($totalStudents / $classrooms->count());

        foreach ($classrooms as $classroom) {
            $studentsForThisClassroom = min($studentsPerClassroom, $totalStudents - $studentIndex);

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
    }
}