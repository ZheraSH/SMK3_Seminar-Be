<?php

namespace App\Http\Controllers\Api\Student;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\StudentClassroomInfoResource;
use App\Services\StudentClassroomService;

class StudentClassroomController extends Controller
{
    private StudentClassroomService $classroomService;

    public function __construct(StudentClassroomService $classroomService)
    {
        $this->classroomService = $classroomService;
    }

    public function getClassroomInfo()
    {
        try {
            $studentId = auth()->user()->student->id;
            
            $data = $this->classroomService->getClassroomInfo($studentId);

            return ResponseHelper::success(
                new StudentClassroomInfoResource($data),
                'Informasi kelas berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}