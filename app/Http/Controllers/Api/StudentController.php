<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Helpers\CrudHelper;
use Illuminate\Support\Str;

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
            'nama' => 'required|string|max:255',
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
            'kelas' => 'nullable|string|max:50',
            'jurusan' => 'nullable|string|max:100',
        ]);

        // 1. Buat akun user otomatis
        $user = User::create([
            'name' => $validated['nama'],
            'email' => Str::slug($validated['nama']) . rand(100, 999) . '@gmail.com',
            'password' => bcrypt('password123'),
        ]);

        // 2. Assign role siswa (Spatie Permission)
        $user->assignRole('siswa');

        // 3. Buat profil student
        $student = Student::create([
            'user_id' => $user->id,
            'nisn' => $validated['nisn'],
            'religion_id' => $validated['religion_id'] ?? null,
            'birthdate' => $validated['birthdate'] ?? null,
            'birthplace' => $validated['birthplace'] ?? null,
            'address' => $validated['address'] ?? null,
            'nik' => $validated['nik'] ?? null,
            'no_kk' => $validated['no_kk'] ?? null,
            'no_birth_certificate' => $validated['no_birth_certificate'] ?? null,
            'order_child' => $validated['order_child'] ?? null,
            'count_siblings' => $validated['count_siblings'] ?? null,
            'point' => $validated['point'] ?? 0,
            'class' => $validated['kelas'] ?? null,
            'major' => $validated['jurusan'] ?? null,
        ]);

        $student->load(['user', 'religion']);

        return ResponseHelper::success($student, 'Student & user account created successfully');
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
            'class' => 'sometimes|nullable|string|max:50',
            'major' => 'sometimes|nullable|string|max:100',
        ]);

        $updated = CrudHelper::update($student, $validated);

        if (!$updated) {
            return ResponseHelper::error('Failed to update student');
        }

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
