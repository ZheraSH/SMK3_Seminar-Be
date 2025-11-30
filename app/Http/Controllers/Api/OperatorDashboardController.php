<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OperatorDashboardService;
use App\Helpers\ResponseHelper;

class OperatorDashboardController extends Controller
{
    private OperatorDashboardService $operatorDashboardService;

    public function __construct(OperatorDashboardService $operatorDashboardService)
    {
        $this->operatorDashboardService = $operatorDashboardService;
    }

    public function getMaster()
    {
        try {
            $data = $this->operatorDashboardService->getMaster();
            
            return ResponseHelper::success(
                $data,
                'Dashboard counters retrieved successfully'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(
                $th->getMessage(),
                $th->getCode() ?: 500
            );
        }
    }

    public function getRfidTap()
    {
        try {
            $data = $this->operatorDashboardService->getRfidTap();
            
            return ResponseHelper::success(
                $data,
                'Recent RFID activities retrieved successfully'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(
                $th->getMessage(),
                $th->getCode() ?: 500
            );
        }
    }

    public function getStatistics()
    {
        try {
            $data = $this->operatorDashboardService->getStatistics();
            
            return ResponseHelper::success(
                $data,
                'Weekly statistics retrieved successfully'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(
                $th->getMessage(),
                $th->getCode() ?: 500
            );
        }
    }
}