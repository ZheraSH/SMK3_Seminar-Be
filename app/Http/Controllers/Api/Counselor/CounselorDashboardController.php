<?php

namespace App\Http\Controllers\Api\Counselor;

use App\Http\Controllers\Controller;
use App\Services\CounselorDashboardService;

class CounselorDashboardController extends Controller
{
    public function __construct(
        private CounselorDashboardService $dashboardService
    ) {}

    public function index()
    {
        return response()->json([
            'status' => true,
            'message' => 'Dashboard BK',
            'data' => $this->dashboardService->getDashboardData()
        ]);
    }
}
