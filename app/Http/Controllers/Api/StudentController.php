<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Contracts\Repositories\StudentRepository;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Services\StudentService;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Throwable;

class StudentController extends Controller
{
    private StudentService $studentService;
    private StudentRepository $studentRepository;

    public function __construct(StudentService $studentService, StudentRepository $studentRepository)
    {
        $this->studentService = $studentService;
        $this->studentRepository = $studentRepository;
    }

    public function index(Request $request)
    {
        try {
            $students = $this->studentRepository->search($request)
                ->load(['user', 'religion']);

            return ResponseHelper::success(
                StudentResource::collection($students),
                'List Data Siswa Berhasil Diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
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
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function show(Student $student)
    {
        try {
            return ResponseHelper::success(
                new StudentResource($student->load(['user', 'religion'])),
                'Detail Data Siswa Berhasil Diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        try {
            $updated = $this->studentService->update($student, $request);
            return ResponseHelper::success(
                new StudentResource($updated),
                'Data Siswa Berhasil Diperbarui'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function destroy(Student $student)
    {
        try {
            $deleted = $this->studentService->delete($student);

            if (!$deleted) {
                return ResponseHelper::notFound();
            }
            return ResponseHelper::success(null, 'Data Siswa Berhasil Dihapus', 200);
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }
}