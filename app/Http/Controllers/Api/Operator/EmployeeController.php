<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\StoreEmployeeRequest;
use App\Http\Requests\Operator\UpdateEmployeeRequest;
use App\Http\Requests\Operator\ImportEmployeeRequest;
use App\Http\Resources\Operator\EmployeeResource;
use App\Services\Operator\EmployeeService;
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

            return ResponseHelper::pagination(
                $data, 
                EmployeeResource::class, 
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
            $data = $this->employeeService->update($employee->id, $request);

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
            $this->employeeService->delete($employee->id);

            return ResponseHelper::success(
                null,
                'Data karyawan berhasil dihapus'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function import(ImportEmployeeRequest $request)
    {
        try {
            $result = $this->employeeService->importEmployees($request->file('file'));

            if ($result['failed']) {
                return ResponseHelper::error(
                    'Gagal mengimport guru. Semua data dibatalkan, silakan perbaiki error berikut.',
                    422,
                    [
                        'imported_count' => 0,
                        'error_count'    => count($result['errors']),
                        'errors'         => $result['errors'],
                    ]
                );
            }

            return ResponseHelper::success(
                [
                    'imported_count' => $result['imported_count'],
                    'error_count'    => 0,
                    'errors'         => [],
                ],
                "Berhasil mengimport {$result['imported_count']} guru ke sistem.",
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}