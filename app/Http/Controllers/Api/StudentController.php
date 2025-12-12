<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\StoreStudentRequest;
use App\Http\Requests\Operator\UpdateStudentRequest;
use App\Http\Resources\Operator\StudentResource;
use App\Services\Operator\StudentService;
use App\Models\Student;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    private StudentService $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->studentService->getWithFilter($request);

            return ResponseHelper::pagination(
                $data, 
                StudentResource::class, 
                'List data siswa berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::notFound('List data siswa gagal diambil');
        }
    }

    public function store(StoreStudentRequest $request)
    {
        try {
            $data = $this->studentService->store($request);
            
            return ResponseHelper::success(
                new StudentResource($data),
                'Data siswa berhasil dibuat',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function show(string $id)
    {
        try {
            $data = $this->studentService->show($id);
            
            return ResponseHelper::success(
                new StudentResource($data),
                'Detail data siswa berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::notFound('detail data siswa tidak ditemukan');
        }
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        try {
            $data = $this->studentService->update($student->id, $request);
            
            return ResponseHelper::success(
                new StudentResource($data),
                'Data siswa berhasil diperbarui'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function destroy(Student $student)
    {
        try {
            $this->studentService->delete($student->id);

            return ResponseHelper::success(
                null,
                'Data siswa berhasil dihapus'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}