<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Helpers\ResponseHelper;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Services\Contracts\StudentServiceInterface;

class StudentController extends Controller
{
    public function __construct(private StudentServiceInterface $studentService) {}

    public function index()
    {
        $students = $this->studentService->listAll();
        return ResponseHelper::success($students, 'Students retrieved successfully');
    }

    public function store(StoreStudentRequest $request)
    {
        $student = $this->studentService->createWithUser($request->validated());
        return ResponseHelper::success($student, 'Student & user account created successfully');
    }

    public function show($id)
    {
        $student = $this->studentService->findById((int) $id);

        if (!$student) {
            return ResponseHelper::notFound('Student not found');
        }

        return ResponseHelper::success($student, 'Student retrieved successfully');
    }

    public function update(UpdateStudentRequest $request, $id)
    {
        $student = Student::find($id);

        if (!$student) {
            return ResponseHelper::notFound('Student not found');
        }

        $updated = $this->studentService->update($student, $request->validated());
        return ResponseHelper::success($updated, 'Student updated successfully');
    }

    public function destroy($id)
    {
        $student = Student::find($id);

        if (!$student) {
            return ResponseHelper::notFound('Student not found');
        }

        if (!$this->studentService->delete($student)) {
            return ResponseHelper::error('Failed to delete student');
        }

        return ResponseHelper::success(null, 'Student deleted successfully');
    }
}
