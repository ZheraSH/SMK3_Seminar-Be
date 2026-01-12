<?php

namespace App\Http\Controllers\Api\Counselor;

use App\Http\Controllers\Controller;
use App\Services\Counselor\CounselorDashboardService;
use App\Helpers\ResponseHelper;
use App\Http\Resources\Counselor\CounselorAttendanceCountResource;
use App\Http\Resources\Counselor\CounselorHighAlphaStudentResource;
use Illuminate\Http\Request;

class CounselorDashboardController extends Controller
{
    private CounselorDashboardService $service;

    public function __construct(CounselorDashboardService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        try {
            $data = $this->service->getAttendanceCounts();
            return ResponseHelper::success(
                new CounselorAttendanceCountResource($data),
                'Statistic attendance'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function highAlphaStudents(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);
            $data = $this->service->getHighAlphaStudents($limit);
            return ResponseHelper::success(
                CounselorHighAlphaStudentResource::collection($data),
                'Students with high alpha'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}
