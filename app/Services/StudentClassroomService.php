<?php

namespace App\Services;

use App\Contracts\Repositories\StudentRepository;
use App\Models\Student;

class StudentClassroomService
{
    private StudentRepository $studentRepository;

    public function __construct(StudentRepository $studentRepository)
    {
        $this->studentRepository = $studentRepository;
    }

    public function getClassroomInfo(string $studentId): array
    {
        $student = $this->studentRepository->getClassroomInfo($studentId);
    
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
            ->with(['student:id,nisn,user_id,image', 'student.user:id,name'])
            ->paginate(10);
    
        return [
            'student' => $student,
            'classroom' => $classroom,
            'classmates' => $classmates
        ];
    }
}