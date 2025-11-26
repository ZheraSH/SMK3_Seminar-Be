<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use App\Http\Resources\TeacherDashboardOverviewResource;
use App\Http\Resources\TeacherScheduleWithAttendanceResource;
use App\Http\Resources\TeacherClassroomListResource;
use App\Services\TeacherDashboardService;
use Illuminate\Http\Request;

class TeacherDashboardController extends Controller
{
    private TeacherDashboardService $teacherDashboardService;

    public function __construct(TeacherDashboardService $teacherDashboardService)
    {
        $this->teacherDashboardService = $teacherDashboardService;
    }

    public function getOverview(Request $request)
    {
        try {
            $teacherId = auth()->user()->employee->id;

            $overview = $this->teacherDashboardService->getDashboardOverviewWithValidation($request, $teacherId);

            return ResponseHelper::success(
                new TeacherDashboardOverviewResource($overview),
                'Dashboard guru berhasil diambil'
            );

        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function getTodaySchedule(Request $request)
    {
        try {
            $teacherId = auth()->user()->employee->id;

            $schedule = $this->teacherDashboardService->getTodayScheduleWithValidation($request, $teacherId);

            if ($schedule->isEmpty()) {
                return ResponseHelper::success(
                    [],
                    'Tidak ada jadwal mengajar untuk hari ini'
                );
            }

            return ResponseHelper::success(
                TeacherScheduleWithAttendanceResource::collection($schedule),
                'Jadwal mengajar hari ini berhasil diambil'
            );

        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function getClassroomList(Request $request)
    {
        try {
            $teacherId = auth()->user()->employee->id;

            $classroomList = $this->teacherDashboardService->getTodayClassroomListWithValidation($request, $teacherId);

            if (empty($classroomList)) {
                return ResponseHelper::success(
                    [],
                    'Tidak ada jadwal mengajar untuk hari ini'
                );
            }

            return ResponseHelper::success(
                TeacherClassroomListResource::collection($classroomList),
                'Daftar kelas yang diajar hari ini berhasil diambil'
            );

        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function getClassroomAttendanceSummary(Request $request, $classroom_id)
    {
        try {
            $teacherId = auth()->user()->employee->id;

            $attendanceSummary = $this->teacherDashboardService->getClassroomAttendanceSummaryWithValidationAndRequest(
                $request,
                $teacherId,
                $classroom_id
            );

            return ResponseHelper::success(
                $attendanceSummary,
                'Ringkasan absensi kelas berhasil diambil'
            );

        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}