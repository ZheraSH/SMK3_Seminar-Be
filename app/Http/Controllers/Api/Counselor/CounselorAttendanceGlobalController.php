<?php

namespace App\Http\Controllers\Api\Counselor;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceGlobalResource;
use App\Services\AttendanceGlobalService;
use Illuminate\Http\Request;

class CounselorAttendanceGlobalController extends Controller
{
    protected AttendanceGlobalService $service;

    public function __construct(AttendanceGlobalService $service)
    {
        $this->service = $service;
    }

   public function index(Request $request)
{
    $filters = $request->only([
        'classroom_id', 
        'major_code', 
        'month', 
        'year', 
        'per_page'
    ]);

    $data = $this->service->getStatistics($filters);

    return ResponseHelper::success([
        'summary'       => $data['summary'],
        'proportion'    => $data['proportion'],
        'monthly_trend' => $data['monthly_trend'],
        'logs'          => AttendanceGlobalResource::collection($data['logs']),
        'pagination'    => $data['pagination'],
    ]);
}

}
