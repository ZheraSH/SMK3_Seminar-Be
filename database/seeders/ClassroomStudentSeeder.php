<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\Student;
use Illuminate\Database\Seeder;

class ClassroomStudentSeeder extends Seeder
{
    public function run(): void
    {
        $classrooms = Classroom::all();
        $students = Student::inRandomOrder()->get();
        if ($classrooms->isEmpty() || $students->isEmpty()) {
            return;
        }

        $i = 0;
        foreach ($students as $student) {
            $classroom = $classrooms[$i % $classrooms->count()];
            ClassroomStudent::firstOrCreate([
                'classroom_id' => $classroom->id,
                'student_id' => $student->id,
            ]);
            $i++;
        }
    }
}


