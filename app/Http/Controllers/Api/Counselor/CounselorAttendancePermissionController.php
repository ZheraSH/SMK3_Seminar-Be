<?php

namespace App\Http\Controllers\Api\Counselor;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\AttendancePermissionResource;
use App\Services\AttendancePermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CounselorAttendancePermissionController extends Controller
{
    private AttendancePermissionService $service;

    public function __construct(AttendancePermissionService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $permissions = $this->service->getCounselorPermissions($request);

            return ResponseHelper::success(
                AttendancePermissionResource::collection($permissions),
                'Data izin berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function pending(): JsonResponse
    {
        try {
            $permissions = $this->service->getPendingPermissions();

            return ResponseHelper::success(
                AttendancePermissionResource::collection($permissions),
                'Data izin pending berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $permission = $this->service->getPermissionDetail($id);

            return ResponseHelper::success(
                new AttendancePermissionResource($permission),
                'Detail izin berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 404);
        }
    }

    public function approve(string $id): JsonResponse
    {
        try {
            $counselorId = auth()->user()->employee->id;
            $this->service->approvePermission($id, $counselorId);

            return ResponseHelper::success(null, 'Izin berhasil disetujui');
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function reject(string $id): JsonResponse
    {
        try {
            $counselorId = auth()->user()->employee->id;
            $this->service->rejectPermission($id, $counselorId);

            return ResponseHelper::success(null, 'Izin berhasil ditolak');
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}