<?php

namespace App\Http\Controllers\Api\Homeroom_teacher;

use App\Http\Controllers\Controller;
use App\Services\HomeroomTeacher\HomeroomTeacherService;
use App\Http\Resources\Homeroom_teacher\SummaryClassResource;
use App\Http\Resources\Homeroom_teacher\WeeklyAttendanceStatisticsResource;
use App\Http\Resources\Homeroom_teacher\DailyStudentAttendanceResource;
use App\Http\Requests\Homeroom_teacher\SummaryClassRequest;
use App\Http\Requests\Homeroom_teacher\DailyAttendanceRequest;
use App\Http\Requests\Homeroom_teacher\WeeklyStatisticsRequest;
use App\Helpers\ResponseHelper;


class HomeroomTeachersController extends Controller
{
    protected HomeroomTeacherService $homeroomTeacherService;

    public function __construct(HomeroomTeacherService $homeroomTeacherService)
    {
        $this->homeroomTeacherService = $homeroomTeacherService;
    }

    public function getSummaryClass(SummaryClassRequest $request)
    {
        try {
            $teacher = $request->user();
            $date = $request->input('date');

            $data = $this->homeroomTeacherService->getDailySummary($teacher, $date);

            return ResponseHelper::success(
                new SummaryClassResource($data),
                'Ringkasan kelas berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500
            );
        }
    }

    public function getWeeklyAttendanceStatistics(WeeklyStatisticsRequest $request)
    {
        try {
            $teacher = $request->user();
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $data = $this->homeroomTeacherService->getWeeklyStatistics($teacher, $startDate, $endDate);

            return ResponseHelper::success(
                new WeeklyAttendanceStatisticsResource($data),
                'Statistik mingguan berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500
            );
        }
    }

    public function getDailyStudentAttendance(DailyAttendanceRequest $request)
    {
        try {
            $teacher = $request->user();
            $date = $request->input('date');
            $perPage = $request->input('per_page', 10);

            $data = $this->homeroomTeacherService->getDailyAttendance($teacher, $date, $perPage);

            return ResponseHelper::success([
                'summary' => new SummaryClassResource($data['summary']),
                'students' => DailyStudentAttendanceResource::collection($data['students']),
                'pagination' => $data['pagination'],
            ], 'Data kehadiran harian berhasil diambil');
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500
            );
        }
    }
}