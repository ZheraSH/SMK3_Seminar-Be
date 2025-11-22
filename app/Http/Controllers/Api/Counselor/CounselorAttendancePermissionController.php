<?php

namespace App\Http\Controllers\Api\Counselor;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\AttendancePermissionResource;
use App\Services\AttendancePermissionService;
use Illuminate\Http\Request;

class CounselorAttendancePermissionController extends Controller
{
    private AttendancePermissionService $attendancePermissionService;

    public function __construct(AttendancePermissionService $attendancePermissionService)
    {
        $this->attendancePermissionService = $attendancePermissionService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->attendancePermissionService->getCounselorPermissions($request);
            
            return ResponseHelper::pagination(
                $data, 
                AttendancePermissionResource::class, 
                'Data izin berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function pending()
    {
        try {
            $data = $this->attendancePermissionService->getPendingPermissions();

            return ResponseHelper::success(
                AttendancePermissionResource::collection($data),
                'Data izin pending berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function show(string $id)
    {
        try {
            $data = $this->attendancePermissionService->getPermissionDetail($id);

            return ResponseHelper::success(
                new AttendancePermissionResource($data),
                'Detail izin berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 404);
        }
    }

    public function approve(string $id)
    {
        try {
            $counselorId = auth()->user()->employee->id;
            $data = $this->attendancePermissionService->approvePermission($id, $counselorId);

            return ResponseHelper::success(
                new AttendancePermissionResource($data),
                'Izin berhasil disetujui'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function reject(string $id)
    {
        try {
            $counselorId = auth()->user()->employee->id;
            $data = $this->attendancePermissionService->rejectPermission($id, $counselorId);

            return ResponseHelper::success(
                new AttendancePermissionResource($data),
                'Izin berhasil ditolak'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}