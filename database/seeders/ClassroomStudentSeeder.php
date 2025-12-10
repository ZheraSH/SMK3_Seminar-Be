<?php

namespace Database\Seeders;

use App\Enums\StudentStatusEnum;
use App\Models\Classroom;
use App\Models\ClassroomStudents;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClassroomStudentSeeder extends Seeder
{
    public function run(): void
    {
        $classrooms = Classroom::all();
        $students = Student::all();

        if ($classrooms->isEmpty() || $students->isEmpty()) {
            $this->command->error('Classrooms or Students not found');
            return;
        }

        $classroomStudentData = [];
        $studentIndex = 0;
        $studentsPerClass = ceil($students->count() / $classrooms->count());

        foreach ($classrooms as $classroom) {
            for ($i = 0; $i < $studentsPerClass; $i++) {
                if ($studentIndex >= $students->count()) break;

                $student = $students[$studentIndex];
                
                $classroomStudentData[] = [
                    'id' => Str::uuid(),
                    'classroom_id' => $classroom->id,
                    'student_id' => $student->id,
                    'status' => StudentStatusEnum::ACTIVE->value,

                ];

                $studentIndex++;
            }
        }

        ClassroomStudents::insert($classroomStudentData);
        $this->command->info("Assigned {$studentIndex} students to classrooms");
    }
}