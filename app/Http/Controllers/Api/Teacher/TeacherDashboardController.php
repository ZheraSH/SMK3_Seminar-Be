<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;

use App\Services\Teacher\TeacherDashboardService;

class TeacherDashboardController extends Controller
{
    private TeacherDashboardService $teacherDashboardService;

    public function __construct(TeacherDashboardService $teacherDashboardService)
    {
        $this->teacherDashboardService = $teacherDashboardService;
    }

}