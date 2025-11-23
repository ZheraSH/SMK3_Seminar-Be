<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use App\Http\Resources\TeacherScheduleResource;
use App\Services\TeacherScheduleService;
use Illuminate\Http\Request;

class TeacherScheduleController extends Controller
{
    private TeacherScheduleService $teacherScheduleService;

    public function __construct(TeacherScheduleService $teacherScheduleService)
    {
        $this->teacherScheduleService = $teacherScheduleService;
    }

    public function getDailySchedule(Request $request)
    {
        try {
            $request->validate([
                'date' => 'required|date',
            ]);

            $teacherId = auth()->user()->employee->id;
            
            $schedule = $this->teacherScheduleService->getDailySchedule(
                $teacherId,
                $request->date
            );

            return ResponseHelper::success(
                TeacherScheduleResource::collection($schedule),
                'Jadwal mengajar berhasil diambil'
            );

        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), 400);
        }
    }

    public function getClassroomSchedule(Request $request)
    {
        try {
            $request->validate([
                'classroom_id' => 'required|exists:classrooms,id',
                'date' => 'required|date',
            ]);

            $schedule = $this->teacherScheduleService->getClassroomSchedule(
                $request->classroom_id,
                $request->date
            );

            return ResponseHelper::success(
                TeacherScheduleResource::collection($schedule),
                'Jadwal kelas berhasil diambil'
            );

        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), 400);
        }
    }
}