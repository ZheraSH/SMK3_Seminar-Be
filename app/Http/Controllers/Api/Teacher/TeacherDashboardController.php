<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use App\Http\Resources\TeacherClassroomListResource;
use App\Http\Resources\TeacherScheduleWithAttendanceResource;
use App\Services\Teacher\TeacherDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherDashboardController extends Controller
{
    private TeacherDashboardService $teacherDashboardService;

    public function __construct(TeacherDashboardService $teacherDashboardService)
    {
        $this->teacherDashboardService = $teacherDashboardService;
    }

    public function getClassroomList(Request $request): JsonResponse
    {
        try {
            $teacherId = auth()->user()->employee->id;
            $date = $request->date ?? now()->format('Y-m-d');
            
            $date = $this->teacherDashboardService->validateDate($date);
            $classroomList = $this->teacherDashboardService->getClassroomList($teacherId, $date);

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

            $date = $this->teacherDashboardService->validateDate($date);
            $schedules = $this->teacherDashboardService->getTodaySchedule($teacherId, $date);

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