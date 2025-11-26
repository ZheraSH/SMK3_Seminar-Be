<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use App\Http\Requests\GetCrossCheckDataRequest;
use App\Http\Requests\CrossCheckAttendanceRequest;
use App\Http\Requests\GetClassroomScheduleRequest;
use App\Http\Resources\TeacherCrossCheckDataResource;
use App\Http\Resources\TeacherClassroomScheduleResource;
use App\Http\Resources\TeacherScheduleWithAttendanceResource;
use App\Services\TeacherAttendanceService;
use Illuminate\Http\JsonResponse;

class TeacherAttendanceController extends Controller
{
    private TeacherAttendanceService $teacherAttendanceService;

    public function __construct(TeacherAttendanceService $teacherAttendanceService)
    {
        $this->teacherAttendanceService = $teacherAttendanceService;
    }

    public function getClassroomSchedule(GetClassroomScheduleRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $schedules = $this->teacherAttendanceService->getClassroomSchedule(
            $validated['classroom_id'],
            $validated['date']
        );
    
        return ResponseHelper::success(
            TeacherClassroomScheduleResource::collection($schedules),
            'Jadwal kelas berhasil diambil'
        );
    }

    public function getCrossCheckData(GetCrossCheckDataRequest $request): JsonResponse
    {
        $teacherId = auth()->user()->employee->id;
        $validated = $request->validated();
        $data = $this->teacherAttendanceService->getCrossCheckData(
            $teacherId,
            $validated['classroom_id'],
            $validated['date'],
            $validated['lesson_order']
        );

        return ResponseHelper::success(
            new TeacherCrossCheckDataResource($data),
            'Data cross-check berhasil diambil'
        );
    }

    public function submitCrossCheck(CrossCheckAttendanceRequest $request): JsonResponse
    {
        $teacherId = auth()->user()->employee->id;
        $attendances = $this->teacherAttendanceService->submitCrossCheck(
            $request->validated(),
            $teacherId
        );
        return ResponseHelper::success(
            $attendances,
            'Absensi cross-check berhasil disimpan',
            201
        );
    }

    public function getScheduleWithAttendanceStatus(GetCrossCheckDataRequest $request): JsonResponse
    {
        $teacherId = auth()->user()->employee->id;
        $validated = $request->validated();

        $schedules = $this->teacherAttendanceService->getScheduleWithAttendanceStatus(
            $teacherId,
            $validated['date']
        );

        return ResponseHelper::success(
            TeacherScheduleWithAttendanceResource::collection($schedules),
            'Jadwal mengajar dengan status absensi berhasil diambil'
        );
    }
}
