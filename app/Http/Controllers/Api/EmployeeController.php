<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Services\EmployeeService;
use App\Models\Employee;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    private EmployeeService $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->employeeService->getWithFilter($request);

            if ($request->has('page')) {
                return ResponseHelper::pagination($data, EmployeeResource::class, 'Daftar karyawan berhasil diambil');
            }

            return ResponseHelper::success(
                EmployeeResource::collection($data),
                'Daftar karyawan berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function store(StoreEmployeeRequest $request)
    {
        try {
            $data = $this->employeeService->store($request);
            $data->load(['user.roles', 'religion', 'subjects']);

            return ResponseHelper::success(
                new EmployeeResource($data),
                'Data karyawan berhasil dibuat',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function show(string $id)
    {
        try {
            $data = $this->employeeService->show($id);
            return ResponseHelper::success(
                new EmployeeResource($data),
                'Detail data karyawan berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::notFound('Data karyawan tidak ditemukan');
        }
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        try {
            $data = $this->employeeService->update($employee, $request);

            return ResponseHelper::success(
                new EmployeeResource($data),
                'Data karyawan berhasil diperbarui'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function destroy(Employee $employee)
    {
        try {
            $this->employeeService->delete($employee);

            return ResponseHelper::success(
                null,
                'Data karyawan berhasil dihapus'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}