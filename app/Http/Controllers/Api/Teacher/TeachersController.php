<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use App\Http\Requests\Teacher\CrossCheckAttendanceRequest;
use App\Http\Requests\Teacher\GetCrossCheckDataRequest;
use App\Http\Resources\Teacher\TeacherCrossCheckDataResource;
use App\Http\Resources\Teacher\TeacherClassroomResource;
use App\Http\Resources\Teacher\TeacherScheduleResource;
use App\Services\Teacher\TeacherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeachersController extends Controller
{
    private TeacherService $teacherService;

    public function __construct(TeacherService $teacherService)
    {
        $this->teacherService = $teacherService;
    }

    public function getTodaySchedule(): JsonResponse
    {
        try {
            $teacherId = auth()->user()->employee->id;
            $date = now()->format('Y-m-d');
            $schedules = $this->teacherService->getDailySchedule($teacherId, $date);

            return ResponseHelper::success(
                TeacherScheduleResource::collection($schedules),
                'Jadwal mengajar hari ini berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function getScheduleByDay(string $day): JsonResponse
    {
        try {
            $teacherId = auth()->user()->employee->id;
            $schedules = $this->teacherService->getScheduleByDay($teacherId, $day);

            return ResponseHelper::success(
                TeacherScheduleResource::collection($schedules),
                'Jadwal mengajar berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function getTodayClassrooms(): JsonResponse
    {
        try {
            $teacherId = auth()->user()->employee->id;
            $today = now()->locale('id');
            $dayName = strtolower($today->dayName);

            $classrooms = $this->teacherService->getTeacherClassroomsByDay($teacherId, $dayName);

            return ResponseHelper::success(
                TeacherClassroomResource::collection($classrooms),
                'Daftar kelas mengajar hari ini berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function getClassroomsByDay(string $day): JsonResponse
    {
        try {
            $teacherId = auth()->user()->employee->id;
            $classrooms = $this->teacherService->getClassroomsByDay($teacherId, $day);

            return ResponseHelper::success(
                TeacherClassroomResource::collection($classrooms),
                'Daftar kelas mengajar berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function getAttendanceForm(GetCrossCheckDataRequest $request): JsonResponse
    {
        try {
            $teacherId = auth()->user()->employee->id;

            $data = $this->teacherService->getCrossCheckData(
                $teacherId,
                $request->validated('classroom_id'),
                $request->validated('date'),
                $request->validated('lesson_order'),
                $request
            );

            return ResponseHelper::success(
                new TeacherCrossCheckDataResource($data),
                'Data absensi kelas berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function submitAttendance(CrossCheckAttendanceRequest $request): JsonResponse
    {
        try {
            $teacherId = auth()->user()->employee->id;

            $result = $this->teacherService->submitCrossCheck($request->validated(), $teacherId);

            return ResponseHelper::success(
                $result,
                'Absensi berhasil disimpan',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}
