<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Contracts\Repositories\EmployeeRepository;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Services\EmployeeService;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Throwable;

class EmployeeController extends Controller
{
    private EmployeeService $employeeService;
    private EmployeeRepository $employeeRepository;

    public function __construct(EmployeeService $employeeService, EmployeeRepository $employeeRepository)
    {
        $this->employeeService = $employeeService;
        $this->employeeRepository = $employeeRepository;
    }

    public function index(Request $request)
    {
        try {
            $employees = $this->employeeRepository->search($request)
                ->load(['user', 'religion']);

            return ResponseHelper::success(
                EmployeeResource::collection($employees),
                'Daftar Karyawan berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function store(StoreEmployeeRequest $request)
    {
        try {
            $employee = $this->employeeService->store($request);
            return ResponseHelper::success(
                new EmployeeResource($employee),
                'Data Karyawan berhasil dibuat',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function show(Employee $employee)
    {
        try {
            return ResponseHelper::success(
                new EmployeeResource($employee->load('user', 'religion')),
                'Detail Data Karyawan berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        try {
            $updated = $this->employeeService->update($employee, $request);
            return ResponseHelper::success(
                new EmployeeResource($updated),
                'Data Karyawan berhasil diperbarui'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function destroy(Employee $employee)
    {
        try {
            $deleted = $this->employeeService->delete($employee);

            if (!$deleted) {
                return ResponseHelper::notFound();
            }
            return ResponseHelper::success(null, 'Data Karyawan berhasil dihapus',200);
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }
}