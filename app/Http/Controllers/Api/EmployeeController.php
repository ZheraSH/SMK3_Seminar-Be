<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Helpers\CrudHelper;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with(['user', 'religion'])->get();
        return ResponseHelper::success($employees);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'nip' => 'required|unique:employees,nip',
            'employment_status' => 'required|string',
            'religion_id' => 'required|exists:religions,id',
        ]);

        $employee = CrudHelper::create(new Employee, $validated);

        if (!$employee) return ResponseHelper::error('Failed to create employee');

        return ResponseHelper::success($employee, 'Employee created successfully');
    }

    public function show($id)
    {
        $employee = Employee::with(['user', 'religion'])->findOrFail($id);
        return ResponseHelper::success($employee);
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'nip' => 'sometimes|unique:employees,nip,' . $employee->id,
            'employment_status' => 'sometimes|string',
            'religion_id' => 'sometimes|exists:religions,id',
        ]);

        $updated = CrudHelper::update($employee, $validated);

        if (!$updated) return ResponseHelper::error('Failed to update employee');

        return ResponseHelper::success($updated, 'Employee updated successfully');
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);

        if (!CrudHelper::delete($employee)) {
            return ResponseHelper::error('Failed to delete employee');
        }

        return ResponseHelper::success(null, 'Employee deleted successfully');
    }
}
