<?php

namespace App\Services;

use App\Contracts\Interfaces\ClassroomStudentsInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\ClassroomStudents;
use App\Enums\ClassroomStudentStatusEnum;
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
