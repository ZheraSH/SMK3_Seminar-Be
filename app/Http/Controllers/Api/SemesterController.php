<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\SemesterHelper;
use Illuminate\Http\JsonResponse;

class SemesterController extends Controller
{
    public function active(): JsonResponse
    {
        $semester = SemesterHelper::getSemester();

        return response()->json([
            'status' => 'success',
            'data' => $semester
        ]);
    }
}
