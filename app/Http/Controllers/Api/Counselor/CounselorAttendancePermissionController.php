<?php

namespace App\Http\Controllers\Api\Counselor;

use App\Http\Controllers\Controller;
use App\Services\AttendancePermissionService;
use App\Http\Resources\AttendancePermissionResource;
use App\Helpers\ResponseHelper;
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
            $data = $this->attendancePermissionService->counselorIndex();
            
            return ResponseHelper::pagination(
                $data, 
                AttendancePermissionResource::class, 
                'Data izin berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function show(string $id)
    {
        try {
            $data = $this->attendancePermissionService->show($id);

            return ResponseHelper::success(
                new AttendancePermissionResource($data),
                'Detail izin berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 404);
        }
    }

    public function pending()
    {
        try {
            $data = $this->attendancePermissionService->getPending();
            
            return ResponseHelper::pagination(
                $data, 
                AttendancePermissionResource::class, 
                'Data izin pending berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function approve(string $id)
    {
        try {
            $data = $this->attendancePermissionService->approve(
                $id,
                auth()->user()->counselor->id
            );

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
            $counselorId = auth()->user()->counselor->id;
            $data = $this->attendancePermissionService->reject($id, $counselorId);

            return ResponseHelper::success(
                new AttendancePermissionResource($data),
                'Izin berhasil ditolak'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}