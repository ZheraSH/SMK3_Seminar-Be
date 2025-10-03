<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Helpers\CrudHelper;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with(['user', 'religion'])->get();
        return ResponseHelper::success($employees, 'Employees retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|unique:employees,nip',
            'employment_status' => 'required|string',
            'religion_id' => 'nullable|exists:religions,id',
            'role' => 'required|in:guru,staff TU', //sesuai daftar role
        ]);

        // 1. Buat akun user otomatis
        $user = User::create([
            'name' => $validated['nama'],
            'email' => Str::slug($validated['nama']) . rand(100, 999) . '@sekolah.com',
            'password' => bcrypt('password123'),
        ]);

        // 2. Assign role (guru atau staff TU)
        $user->assignRole($validated['role']);

        // 3. Buat profil employee
        $employee = Employee::create([
            'user_id' => $user->id,
            'nip' => $validated['nip'],
            'employment_status' => $validated['employment_status'],
            'religion_id' => $validated['religion_id'] ?? null,
        ]);

        $employee->load(['user', 'religion']);

        return ResponseHelper::success($employee, 'Employee & user account created successfully');
    }

    public function show($id)
    {
        $employee = Employee::with(['user', 'religion'])->find($id);

        if (!$employee) {
            return ResponseHelper::notFound('Employee not found');
        }

        return ResponseHelper::success($employee, 'Employee retrieved successfully');
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return ResponseHelper::notFound('Employee not found');
        }

        $validated = $request->validate([
            'nip' => 'sometimes|unique:employees,nip,' . $employee->id,
            'employment_status' => 'sometimes|string',
            'religion_id' => 'sometimes|nullable|exists:religions,id',
        ]);

        $updated = CrudHelper::update($employee, $validated);

        if (!$updated) {
            return ResponseHelper::error('Failed to update employee');
        }

        $updated->load(['user', 'religion']);
        return ResponseHelper::success($updated, 'Employee updated successfully');
    }

    public function destroy($id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return ResponseHelper::notFound('Employee not found');
        }

        if (!CrudHelper::delete($employee)) {
            return ResponseHelper::error('Failed to delete employee');
        }

        return ResponseHelper::success(null, 'Employee deleted successfully');
    }
}
