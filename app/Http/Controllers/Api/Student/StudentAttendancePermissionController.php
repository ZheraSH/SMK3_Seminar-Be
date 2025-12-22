<?php

namespace App\Http\Controllers\Api\Student;

use App\Enums\PermissionStatusEnum;
use App\Http\Controllers\Controller;
use App\Services\AttendancePermissionService;
use App\Http\Resources\AttendancePermissionResource;
use App\Http\Resources\AttendancePermissionDetailResource;
use App\Http\Resources\AttendancePermissionPendingResource;
use App\Http\Requests\StoreAttendancePermissionRequest;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

class StudentAttendancePermissionController extends Controller
{
    private AttendancePermissionService $attendancePermissionService;

    public function __construct(AttendancePermissionService $attendancePermissionService)
    {
        $this->attendancePermissionService = $attendancePermissionService;
    }

    public function index(Request $request)
    {
        try {
            $studentId = auth()->user()->student->id;
            $data = $this->attendancePermissionService->studentIndex($studentId, $request->query('status'));

            return ResponseHelper::pagination(
                $data, 
                AttendancePermissionResource::class, 
                'Data izin berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function store(StoreAttendancePermissionRequest $request)
    {
        try {
            $data = $this->attendancePermissionService->store($request);

            return ResponseHelper::success(
                new AttendancePermissionDetailResource($data),
                'Izin berhasil diajukan',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }    

    public function show(string $id)
    {
        try {
            $data = $this->attendancePermissionService->show($id);

            return ResponseHelper::success(
                new AttendancePermissionDetailResource($data),
                'Detail izin berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 404);
        }
    }
    public function destroy(string $id)
    {
        try {
            $studentId = auth()->user()->student->id;
            $this->attendancePermissionService->delete($id, $studentId);

            return ResponseHelper::success(
                null,
                'Izin berhasil dihapus'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function pending(Request $request)
    {
        try {
            $studentId = auth()->user()->student->id;
            $data = $this->attendancePermissionService->studentIndex($studentId, PermissionStatusEnum::PENDING->value);

            return ResponseHelper::pagination(
                $data, 
                AttendancePermissionPendingResource::class, 
                'Data izin pending berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}