<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Services\Student\StudentDashboardService;
use App\Http\Resources\Student\Dashboard\StudentAttendanceSummaryResource;
use App\Http\Resources\Student\Dashboard\StudentAttendanceMonthlyResource;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    private StudentDashboardService $studentDashboardService;

    public function __construct(StudentDashboardService $studentDashboardService)
    {
        $this->studentDashboardService = $studentDashboardService;
    }

    public function attendanceSummary(Request $request)
    {
        try {
            $data = $this->studentDashboardService->getAttendanceSummary($request->user(), $request);

            return ResponseHelper::success(
                new StudentAttendanceSummaryResource($data),
                'Ringkasan absensi'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function attendanceMonthly(Request $request)
    {
        try {
            $data = $this->studentDashboardService->getMonthlyAttendance($request->user(), $request);

            return ResponseHelper::success(
                StudentAttendanceMonthlyResource::collection($data),
                'Statistik absensi bulanan'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}
