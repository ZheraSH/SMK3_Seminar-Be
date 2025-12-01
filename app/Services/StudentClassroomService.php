<?php

namespace App\Services;

use App\Contracts\Interfaces\StudentInterface;
use App\Models\Student;

class StudentClassroomService
{
    private StudentInterface $student;

    public function __construct(StudentInterface $student)
    {
        $this->student = $student;
    }

    public function getClassroomInfo(string $studentId): array
    {
        $student = $this->student->getClassroomInfo($studentId);
    
        if (!$student) {
            throw new \Exception("Student with ID {$studentId} does not exist", 404);
        }
    
        $activeClassroomStudent = $student->classroomStudents
            ->where('status', 'active')
            ->first();
    
        if (!$activeClassroomStudent || !$activeClassroomStudent->classroom) {
            throw new \Exception("Student is not assigned to any active classroom", 404);
        }
    
        $classroom = $activeClassroomStudent->classroom->loadCount('classroomStudents');
    
        $classmates = $classroom->classroomStudents()
            ->where('student_id', '!=', $studentId)
            ->where('status', 'active')
            ->with(['student:id,nisn,user_id', 'student.user:id,name'])
            ->paginate(10);
    
        return [
            'student' => $student,
            'classroom' => $classroom,
            'classmates' => $classmates
        ];
    }
}