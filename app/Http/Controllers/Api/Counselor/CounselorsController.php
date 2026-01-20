<?php

namespace App\Http\Controllers\Api\Counselor;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\AttendancePermissionResource;
use App\Http\Resources\Counselor\AttendanceGlobalResource;
use App\Http\Resources\Counselor\AttendanceMonthlyResource;
use App\Services\AttendancePermissionService;
use App\Services\Counselor\CounselorService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CounselorsController extends Controller
{
    private CounselorService $counselorService;
    private AttendancePermissionService $attendancePermissionService;

    public function __construct(CounselorService $counselorService, AttendancePermissionService $attendancePermissionService)
    {
        $this->counselorService = $counselorService;
        $this->attendancePermissionService = $attendancePermissionService;
    }
    public function index(Request $request)
    {
        try {
            $data = $this->attendancePermissionService->counselorIndex($request);

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
            $data = $this->attendancePermissionService->approve($id, auth()->user()->employee->id);

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
            $data = $this->attendancePermissionService->reject($id, $counselorId);

            return ResponseHelper::success(
                new AttendancePermissionResource($data),
                'Izin berhasil ditolak'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function attendanceGlobalStats()
    {
        try {
            $data = $this->counselorService->getGlobalAttendanceStats();

            return ResponseHelper::success(
                new AttendanceGlobalResource($data),
                'Statistik kehadiran global berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function attendanceMonthlyStats()
    {
        try {
            $year = request('year', Carbon::now()->year);
            $data = $this->counselorService->getMonthlyAttendanceStats((int) $year);

            return ResponseHelper::success(
                AttendanceMonthlyResource::collection($data),
                'Statistik kehadiran bulanan berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}
