<?php

namespace App\Http\Controllers\Api\Homeroom_teacher;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\DailyAttendanceRequest;
use App\Http\Requests\WeeklyStatisticsRequest;
use App\Http\Requests\SummaryClassRequest;
use App\Http\Resources\SummaryClassResource;
use App\Http\Resources\WeeklyAttendanceStatisticsResource;
use App\Http\Resources\DailyStudentAttendanceResource;
use App\Services\HomeroomTeacherSummaryService;


class HomeroomTeacherSummaryClassController extends Controller
{
    protected HomeroomTeacherSummaryService $service;

    public function __construct(HomeroomTeacherSummaryService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/homeroom-teacher/summary-class?date=2023-12-01

     */
    public function getSummaryClass(SummaryClassRequest $request)
    {
        try {
            $teacher = $request->user();
            $date = $request->input('date');

            $data = $this->service->getDailySummary($teacher, $date);

            return ResponseHelper::success(
                new SummaryClassResource($data),
                'Ringkasan kelas berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(
                $th->getMessage(),
                $th->getCode() ?: 500
            );
        }
    }

    /**
     * GET /api/homeroom-teacher/weekly-attendance?start_date=2023-12-01&end_date=2023-12-07
     * Statistik kehadiran mingguan
     */
    public function getWeeklyAttendanceStatistics(WeeklyStatisticsRequest $request)
    {
        try {
            $teacher = $request->user();
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $data = $this->service->getWeeklyStatistics($teacher, $startDate, $endDate);

            return ResponseHelper::success(
                new WeeklyAttendanceStatisticsResource($data),
                'Statistik mingguan berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(
                $th->getMessage(),
                $th->getCode() ?: 500
            );
        }
    }

    /**
     * GET /api/homeroom-teacher/daily-attendance?date=2023-12-01&per_page=10
     * Daftar kehadiran harian siswa
     */
    public function getDailyStudentAttendance(DailyAttendanceRequest $request)
    {
        try {
            $teacher = $request->user();
            $date = $request->input('date');
            $perPage = $request->input('per_page', 10);

            $data = $this->service->getDailyAttendance($teacher, $date, $perPage);

            return ResponseHelper::success([
                'summary' => new SummaryClassResource($data['summary']),
                'students' => DailyStudentAttendanceResource::collection($data['students']),
                'pagination' => $data['pagination'],
            ], 'Data kehadiran harian berhasil diambil');
        } catch (\Throwable $th) {
            return ResponseHelper::error(
                $th->getMessage(),
                $th->getCode() ?: 500
            );
        }
    }
}