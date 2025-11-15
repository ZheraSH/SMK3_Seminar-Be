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

    public function search(Request $request)
    {
        return $this->classroomStudentsInterface->search($request);
    }

    public function paginate()
    {
        return $this->classroomStudentsInterface->paginate();
    }

    public function get()
    {
        return $this->classroomStudentsInterface->get();
    }
}