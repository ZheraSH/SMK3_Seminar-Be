<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Services\Operator\OperatorDashboardService;
use App\Http\Resources\Operator\Dashboard\DashboardCounterResource;
use App\Http\Resources\Operator\Dashboard\DashboardActivityResource;
use App\Http\Resources\Operator\Dashboard\DashboardTodayAttendanceChartResource;
use App\Http\Resources\Operator\Dashboard\DashboardMonthlyAttendanceChartResource;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

class OperatorDashboardController extends Controller
{
    private OperatorDashboardService $service;

    public function __construct(OperatorDashboardService $service)
    {
        $this->service = $service;
    }

    public function getCounter()
    {
        try {
            $data = $this->service->getMaster();

            return ResponseHelper::success(
                new DashboardCounterResource($data),
                'Counters dashboard'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function getRfidHistory(Request $request)
    {
        try {
            $data = $this->service->getRfidActivities($request);

            return ResponseHelper::pagination(
                $data,
                DashboardActivityResource::class,
                'Aktivitas Tap RFID'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function getStatisticsDay()
    {
        try {
            $data = $this->service->getTodayAttendanceSummary();

            return ResponseHelper::success(
                new DashboardTodayAttendanceChartResource($data),
                'Statistik kehadiran hari ini'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function getStatisticsMonthly()
    {
        try {
            $data = $this->service->getMonthlyAttendanceChart();

            return ResponseHelper::success(
                DashboardMonthlyAttendanceChartResource::collection($data),
                'Statistik kehadiran bulanan'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}