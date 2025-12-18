<?php

namespace App\Http\Controllers\Api\Operator;

use App\Services\Operator\ClassroomStudentsService;
use App\Http\Resources\Operator\ClassroomStudentsResource;
use App\Http\Resources\Operator\AvailableStudentResource;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\AddStudentToClassroomRequest;
use Illuminate\Http\Request;

class ClassroomStudentsController extends Controller
{
    private ClassroomStudentsService $classroomStudentsService;

    public function __construct(ClassroomStudentsService $classroomStudentsService)
    {
        $this->classroomStudentsService = $classroomStudentsService;
    }

    public function index(Request $request, string $classroomId)
    {
        try {
            $data = $this->classroomStudentsService->getByClassroom($classroomId, $request);

            return ResponseHelper::pagination(
                $data,
                ClassroomStudentsResource::class,
                'List data siswa kelas berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function getAvailableStudents(Request $request, string $classroomId)
    {
        try {
            $data = $this->classroomStudentsService->getAvailableStudents($classroomId, $request);

            return ResponseHelper::success(
                AvailableStudentResource::collection($data),
                'Data siswa tersedia berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function store(AddStudentToClassroomRequest $request, string $classroomId)
    {
        try {
            $data = $this->classroomStudentsService->addStudents($classroomId, $request->student_ids);

            return ResponseHelper::success(
                ClassroomStudentsResource::collection($data),
                'Siswa berhasil ditambahkan ke kelas'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function destroy(string $classroomId, string $studentId)
    {
        try {
            $data = $this->classroomStudentsService->removeStudent($classroomId, $studentId);

            return ResponseHelper::success(
                ClassroomStudentsResource::collection($data),
                'Siswa berhasil dihapus dari kelas'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}