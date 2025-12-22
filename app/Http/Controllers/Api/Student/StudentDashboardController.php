<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Services\Student\StudentDashboardService;
use App\Http\Resources\Student\Dashboard\StudentAttendanceSummaryResource;
use App\Http\Resources\Student\Dashboard\StudentAttendanceMonthlyResource;
use App\Helpers\ResponseHelper;

class StudentDashboardController extends Controller
{
    private StudentDashboardService $studentDashboardService;

    public function __construct(StudentDashboardService $studentDashboardService)
    {
        $this->studentDashboardService = $studentDashboardService;
    }

    public function attendanceSummary()
    {
        try {
            $studentId = auth()->user()->student->id;

            $data = $this->studentDashboardService->getAttendanceSummary($studentId);

            return ResponseHelper::success(
                new StudentAttendanceSummaryResource($data),
                'Ringkasan absensi'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function attendanceMonthly()
    {
        try {
            $studentId = auth()->user()->student->id;

            $data = $this->studentDashboardService->getMonthlyAttendance($studentId);

            return ResponseHelper::success(
                StudentAttendanceMonthlyResource::collection($data),
                'Statistik absensi bulanan'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}