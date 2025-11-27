<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use App\Http\Requests\GetCrossCheckDataRequest;
use App\Http\Requests\CrossCheckAttendanceRequest;
use App\Http\Resources\TeacherCrossCheckDataResource;
use App\Http\Resources\TeacherClassroomResource;
use App\Services\TeacherAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherAttendanceController extends Controller
{
    private TeacherAttendanceService $attendanceService;
    public function __construct(TeacherAttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function getTeacherClassrooms(Request $request): JsonResponse
    {
        try {
            $teacherId = auth()->user()->employee->id;
            $date = $request->date ?? now()->format('Y-m-d');
            $classrooms = $this->attendanceService->getTeacherClassrooms($teacherId, $date);
            return ResponseHelper::success(
                TeacherClassroomResource::collection($classrooms),
                'Daftar kelas berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function getCrossCheckData(GetCrossCheckDataRequest $request): JsonResponse
    {
        try {
            $teacherId = auth()->user()->employee->id;
            $validated = $request->validated();
            $data = $this->attendanceService->getCrossCheckData(
                $teacherId,
                $validated['classroom_id'],
                $validated['date'],
                $validated['lesson_order'],
                $request
            );
            return ResponseHelper::success(
                new TeacherCrossCheckDataResource($data),
                'Data cross-check berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function submitCrossCheck(CrossCheckAttendanceRequest $request): JsonResponse
    {
        try {
            $teacherId = auth()->user()->employee->id;
            $attendances = $this->attendanceService->submitCrossCheck(
                $request->validated(),
                $teacherId
            );
            return ResponseHelper::success(
                $attendances,
                'Absensi cross-check berhasil disimpan',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}