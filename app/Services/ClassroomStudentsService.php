<?php

namespace App\Services;

use App\Contracts\Interfaces\ClassroomStudentsInterface;
use App\Contracts\Interfaces\ClassroomInterface;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ClassroomStudentsService
{
    private ClassroomStudentsInterface $classroomStudentsInterface;
    private ClassroomInterface $classroomInterface;

    public function __construct(ClassroomStudentsInterface $classroomStudentsInterface, ClassroomInterface $classroomInterface)
    {
        $this->classroomStudentsInterface = $classroomStudentsInterface;
        $this->classroomInterface = $classroomInterface;
    }

    public function handleGetData(Request $request): LengthAwarePaginator
    {
        if ($request->has('classroom_id') && !empty($request->classroom_id)) {
            return $this->classroomStudentsInterface->getByClassroom($request->classroom_id, $request);
        }

        return $this->classroomStudentsInterface->search($request);
    }

    public function search(Request $request): LengthAwarePaginator
    {
        return $this->classroomStudentsInterface->search($request);
    }

    public function getByClassroom(string $classroomId, Request $request = null): LengthAwarePaginator
    {
        return $this->classroomStudentsInterface->getByClassroom($classroomId, $request);
    }

    public function getAvailableStudents(string $classroomId, Request $request): Collection
    {
        $search = $request->query('search');
        $limit = $request->query('limit', 10);
        
        $classroom = $this->classroomInterface->show($classroomId);
        return $this->classroomInterface->getAvailableStudents($classroom, $search, $limit);
    }

    public function addStudents(string $classroomId, array $studentIds): Classroom
    {
        return $this->classroomInterface->addStudentsToClassroom($classroomId, $studentIds);
    }

    public function removeStudent(string $classroomId, string $studentId): Classroom
    {
        return $this->classroomInterface->removeStudentFromClassroom($classroomId, $studentId);
    }

    public function getActiveStudents(string $classroomId): Collection
    {
        return $this->classroomInterface->getActiveStudents($classroomId);
    }
}