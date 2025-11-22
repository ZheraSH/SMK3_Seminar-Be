<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use App\Http\Requests\CrossCheckAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Services\TeacherAttendanceService;
use Illuminate\Http\Request;

class TeacherAttendanceController extends Controller
{
    private TeacherAttendanceService $teacherAttendanceService;

    public function __construct(TeacherAttendanceService $teacherAttendanceService)
    {
        $this->teacherAttendanceService = $teacherAttendanceService;
    }

    public function getCrossCheckData(Request $request)
    {
        try {
            $request->validate([
                'classroom_id' => 'required|exists:classrooms,id',
                'date' => 'required|date',
                'lesson_order' => 'required|integer|min:2',
            ]);

            $teacherId = auth()->user()->employee->id;
            
            $data = $this->teacherAttendanceService->getCrossCheckData(
                $request->classroom_id,
                $request->date,
                $request->lesson_order
            );

            return ResponseHelper::success($data, 'Data cross-check berhasil diambil');

        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), 400);
        }
    }

    public function submitCrossCheck(CrossCheckAttendanceRequest $request)
    {
        try {
            $teacherId = auth()->user()->employee->id;
            
            $attendances = $this->teacherAttendanceService->submitCrossCheck(
                $request->validated(),
                $teacherId
            );

            return ResponseHelper::success(
                AttendanceResource::collection($attendances),
                'Absensi cross-check berhasil disimpan',
                201
            );

        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), 400);
        }
    }

    public function getTeacherSchedule(Request $request)
    {
        try {
            $request->validate([
                'date' => 'required|date',
            ]);

            $teacherId = auth()->user()->employee->id;
            
            $schedule = $this->teacherAttendanceService->getTeacherSchedule(
                $teacherId,
                $request->date
            );

            return ResponseHelper::success($schedule, 'Jadwal mengajar berhasil diambil');

        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), 400);
        }
    }

    public function getClassroomSummary(Request $request)
    {
        try {
            $request->validate([
                'classroom_id' => 'required|exists:classrooms,id',
                'date' => 'required|date',
            ]);

            $summary = $this->teacherAttendanceService->getClassroomSummary(
                $request->classroom_id,
                $request->date
            );

            return ResponseHelper::success($summary, 'Summary kehadiran berhasil diambil');

        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), 400);
        }
    }
}