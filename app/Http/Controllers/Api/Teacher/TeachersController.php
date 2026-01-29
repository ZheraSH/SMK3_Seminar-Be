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

    public function getTodaySchedule(Request $request): JsonResponse
    {
        try {
            $schedules = $this->teacherService->getTodaySchedule($request->user(), $request);

            return ResponseHelper::success(
                TeacherScheduleResource::collection($schedules),
                'Jadwal mengajar hari ini berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function getScheduleByDay(Request $request, string $day): JsonResponse
    {
        try {
            $schedules = $this->teacherService->getScheduleByDay($request->user(), $request, $day);

            return ResponseHelper::success(
                TeacherScheduleResource::collection($schedules),
                'Jadwal mengajar berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function getTodayClassrooms(Request $request): JsonResponse
    {
        try {
            $classrooms = $this->teacherService->getTodayClassrooms($request->user(), $request);

            return ResponseHelper::success(
                TeacherClassroomResource::collection($classrooms),
                'Daftar kelas mengajar hari ini berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function getClassroomsByDay(Request $request, string $day): JsonResponse
    {
        try {
            $classrooms = $this->teacherService->getClassroomsByDay($request->user(), $request, $day);

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
            $data = $this->teacherService->getCrossCheckData($request->user(), $request);

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
            $result = $this->teacherService->submitCrossCheck($request->user(), $request);

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
