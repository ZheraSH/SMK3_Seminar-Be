<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use App\Http\Resources\TeacherClassroomListResource;
use App\Http\Resources\TeacherScheduleWithAttendanceResource;
use App\Services\TeacherDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherDashboardController extends Controller
{
    public function __construct(
        private TeacherDashboardService $dashboardService
    ) {}

    public function getClassroomList(Request $request): JsonResponse
    {
        try {
            $teacherId = auth()->user()->employee->id;
            $date = $request->date ?? now()->format('Y-m-d');
            
            $date = $this->dashboardService->validateDate($date);
            $classroomList = $this->dashboardService->getClassroomList($teacherId, $date);

            if (empty($classroomList)) {
                return ResponseHelper::success([], 'Tidak ada kelas yang diajar hari ini');
            }

            return ResponseHelper::success(
                TeacherClassroomListResource::collection($classroomList),
                'Daftar kelas berhasil diambil'
            );

        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function getTodaySchedule(Request $request): JsonResponse
    {
        try {
            $teacherId = auth()->user()->employee->id;
            $date = $request->date ?? now()->format('Y-m-d');
            
            $date = $this->dashboardService->validateDate($date);
            $schedules = $this->dashboardService->getTodaySchedule($teacherId, $date);

            if ($schedules->isEmpty()) {
                return ResponseHelper::success([], 'Tidak ada jadwal mengajar untuk hari ini');
            }

            return ResponseHelper::success(
                TeacherScheduleWithAttendanceResource::collection($schedules),
                'Jadwal mengajar hari ini berhasil diambil'
            );

        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}