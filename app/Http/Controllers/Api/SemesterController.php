<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\SemesterHelper;
use App\Helpers\ResponseHelper;

class SemesterController extends Controller
{
    public function active()
    {
        $semester = SemesterHelper::getSemester();

        return ResponseHelper::success(
            $semester,
            'Semester aktif berhasil diambil'
        );
    }
}