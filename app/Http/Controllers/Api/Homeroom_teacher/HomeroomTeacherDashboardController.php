<?php

namespace App\Http\Controllers\Api\Homeroom_teacher;

use App\Http\Controllers\Controller;
use App\Services\Homeroom_Teacher\HomeroomTeacherDashboardService;
use App\Http\Resources\Homeroom_teacher\Dashboard\HomeroomDailyStatsResource;
use App\Http\Resources\Homeroom_teacher\Dashboard\HomeroomRfidLogResource;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;

class HomeroomTeacherDashboardController extends Controller
{
    protected HomeroomTeacherDashboardService $service;

    public function __construct(HomeroomTeacherDashboardService $service)
    {
        $this->service = $service;
    }

    public function indexStats(Request $request)
    {
        try {
            $data = $this->service->getDailyStats($request->user());
            return ResponseHelper::success(
                new HomeroomDailyStatsResource($data),
                'Statistic attendance today'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function rfidLogs(Request $request)
    {
        try {
            $data = $this->service->getTodaysRfidLog($request->user());
            return ResponseHelper::success(
                HomeroomRfidLogResource::collection($data),
                'RFID logs today'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}
