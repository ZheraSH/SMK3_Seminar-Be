<?php

namespace App\Services;

use App\Contracts\Interfaces\ClassroomStudentsInterface;
use Illuminate\Http\Request;

class ClassroomStudentsService
{
    private ClassroomStudentsInterface $classroomStudentsInterface;

    public function __construct(ClassroomStudentsInterface $classroomStudentsInterface)
    {
        $this->classroomStudentsInterface = $classroomStudentsInterface;
    }

    public function handleGetData(Request $request)
    {
        if ($request->has('classroom_id') && !empty($request->classroom_id)) {
            return $this->classroomStudentsInterface->getByClassroom($request->classroom_id, $request);
        }

        if ($request->has('search') && !empty($request->search)) {
            return $this->classroomStudentsInterface->search($request);
        }

        return $this->classroomStudentsInterface->paginate($request);
    }

    public function search(Request $request)
    {
        return $this->classroomStudentsInterface->search($request);
    }

    public function paginate(Request $request = null)
    {
        return $this->classroomStudentsInterface->paginate($request);
    }

    public function get()
    {
        return $this->classroomStudentsInterface->get();
    }

    public function getByClassroom(string $classroomId, Request $request = null)
    {
        return $this->classroomStudentsInterface->getByClassroom($classroomId, $request);
    }
}