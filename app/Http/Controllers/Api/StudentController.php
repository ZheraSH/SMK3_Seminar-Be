<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Services\StudentService;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Exception;

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
            $students = $this->studentService->search($request);
            return ResponseHelper::success(
                StudentResource::collection($students),
                'Student list retrieved successfully'
            );
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function store(StoreStudentRequest $request)
    {
        try {
            $student = $this->studentService->store($request);
            return ResponseHelper::success(
                new StudentResource($student),
                'Data Siswa Berhasil Dibuat',
                201
            );
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function show(Student $student)
    {
        try {
            if (!$student) return ResponseHelper::notFound();

            return ResponseHelper::success(
                new StudentResource($student->load('user', 'religion')),
                'Detail Data Siswa Berhasil Diambil'
            );
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        try {
            $updated = $this->studentService->update($student, $request);

            if (!$updated) return ResponseHelper::notFound();

            return ResponseHelper::success(
                new StudentResource($student->fresh('user', 'religion')),
                'Data Siswa Berhasil Diperbaharui'
            );
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function destroy(Student $student)
    {
        try {
            $deleted = $this->studentService->delete($student);

            if (!$deleted) return ResponseHelper::notFound();

            return ResponseHelper::success(null, 'Data Siswa Berhasil Dihapus');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}