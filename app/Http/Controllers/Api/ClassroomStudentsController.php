<?php

namespace App\Http\Controllers\Api;

use App\Services\ClassroomStudentsService;
use App\Http\Resources\ClassroomStudentsResource;
use App\Http\Resources\AvailableStudentResource;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddStudentToClassroomRequest;
use Illuminate\Http\Request;

class ClassroomStudentsController extends Controller
{
    private ClassroomStudentsService $classroomStudentsService;

    public function __construct(ClassroomStudentsService $classroomStudentsService)
    {
        $this->classroomStudentsService = $classroomStudentsService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->classroomStudentsService->search($request);

            return ResponseHelper::pagination(
                $data,
                ClassroomStudentsResource::class,
                'Data siswa kelas berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function getAvailableStudents(Request $request, string $classroomId)
    {
        try {
            $data = $this->classroomStudentsService->getAvailableStudents($classroomId, $request);

            return ResponseHelper::success(
                AvailableStudentResource::collection($data),
                'Data siswa yang tersedia berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function addStudents(AddStudentToClassroomRequest $request, string $classroomId)
    {
        try {
            $data = $this->classroomStudentsService->addStudents($classroomId, $request->student_ids);

            return ResponseHelper::success(
                ClassroomStudentsResource::collection($data->classroomStudents),
                'Siswa berhasil ditambahkan ke kelas'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function removeStudent(string $classroomId, string $studentId)
    {
        try {
            $data = $this->classroomStudentsService->removeStudent($classroomId, $studentId);

            return ResponseHelper::success(
                ClassroomStudentsResource::collection($data->classroomStudents),
                'Siswa berhasil dihapus dari kelas'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function getActiveStudents(string $classroomId)
    {
        try {
            $data = $this->classroomStudentsService->getActiveStudents($classroomId);

            return ResponseHelper::success(
                $data,
                'Data siswa aktif berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}