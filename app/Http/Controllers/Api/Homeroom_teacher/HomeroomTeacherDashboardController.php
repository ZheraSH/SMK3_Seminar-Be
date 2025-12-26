<?php

namespace App\Http\Controllers\Api\Homeroom_teacher;

use App\Http\Controllers\Controller;
use App\Services\Homeroom_Teacher\HomeroomTeacherDashboardService;
use App\Helpers\ResponseHelper;


class HomeroomTeacherDashboardController extends Controller
{
    protected HomeroomTeacherDashboardService $homeroomTeacherDahsboardService;

    public function __construct(HomeroomTeacherDashboardService $homeroomTeacherDahsboardService)
    {
        $this->homeroomTeacherDahsboardService = $homeroomTeacherDahsboardService;
    }

}