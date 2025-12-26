<?php

namespace App\Http\Controllers\Api\Counselor;

use App\Http\Controllers\Controller;
use App\Services\Counselor\CounselorDashboardService;

class CounselorDashboardController extends Controller
{
    private CounselorDashboardService $counselorDashboardService;

    public function __construct(CounselorDashboardService $counselorDashboardService)
    {
        $this->counselorDashboardService = $counselorDashboardService;
    }

}