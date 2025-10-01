<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Helpers\CrudHelper;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with(['user', 'religion'])->get();
        return ResponseHelper::success($students, 'Students retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'nisn' => 'required|unique:students,nisn',
            'religion_id' => 'nullable|exists:religions,id',
            'birthdate' => 'nullable|date',
            'birthplace' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'nik' => 'nullable|string|max:255',
            'no_kk' => 'nullable|string|max:255',
            'no_birth_certificate' => 'nullable|string|max:255',
            'order_child' => 'nullable|integer|min:1',
            'count_siblings' => 'nullable|integer|min:0',
            'point' => 'nullable|integer|min:0',
        ]);

        $student = CrudHelper::create(new Student, $validated);

        if (!$student) return ResponseHelper::error('Failed to create student');

        $student->load(['user', 'religion']);
        return ResponseHelper::success($student, 'Student created successfully');
    }

    public function show($id)
    {
        $student = Student::with(['user', 'religion'])->find($id);
        
        if (!$student) {
            return ResponseHelper::notFound('Student not found');
        }
        
        return ResponseHelper::success($student, 'Student retrieved successfully');
    }

    public function update(Request $request, $id)
    {
        $student = Student::find($id);
        
        if (!$student) {
            return ResponseHelper::notFound('Student not found');
        }

        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'nisn' => 'sometimes|unique:students,nisn,' . $student->id,
            'religion_id' => 'sometimes|nullable|exists:religions,id',
            'birthdate' => 'sometimes|nullable|date',
            'birthplace' => 'sometimes|nullable|string|max:255',
            'address' => 'sometimes|nullable|string|max:255',
            'nik' => 'sometimes|nullable|string|max:255',
            'no_kk' => 'sometimes|nullable|string|max:255',
            'no_birth_certificate' => 'sometimes|nullable|string|max:255',
            'order_child' => 'sometimes|nullable|integer|min:1',
            'count_siblings' => 'sometimes|nullable|integer|min:0',
            'point' => 'sometimes|nullable|integer|min:0',
        ]);

        $updated = CrudHelper::update($student, $validated);

        if (!$updated) return ResponseHelper::error('Failed to update student');

        $updated->load(['user', 'religion']);
        return ResponseHelper::success($updated, 'Student updated successfully');
    }

    public function destroy($id)
    {
        $student = Student::find($id);
        
        if (!$student) {
            return ResponseHelper::notFound('Student not found');
        }

        if (!CrudHelper::delete($student)) {
            return ResponseHelper::error('Failed to delete student');
        }

        return ResponseHelper::success(null, 'Student deleted successfully');
    }
}
