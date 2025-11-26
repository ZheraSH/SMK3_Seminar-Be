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
            $teacherId = auth()->user()->employee->id;
            $schedule = $this->teacherScheduleService->getDailyScheduleWithValidation($request, $teacherId);
            return ResponseHelper::success(
                TeacherScheduleResource::collection($schedule),
                'Jadwal mengajar berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}