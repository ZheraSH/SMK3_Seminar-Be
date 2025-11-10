<?php

namespace App\Services;

use App\Contracts\Interfaces\ClassroomStudentsInterface;
use Illuminate\Http\Request;

class ClassroomStudentsService
{
    private ClassroomStudentsInterface $classroomStudentsRepository;

    public function __construct(ClassroomStudentsInterface $classroomStudentsRepository)
    {
        $this->classroomStudentsRepository = $classroomStudentsRepository;
    }

    public function search(Request $request)
    {
        return $this->classroomStudentsRepository->search($request);
    }
}
