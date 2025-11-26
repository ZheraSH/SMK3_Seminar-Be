<?php

namespace App\Http\Controllers\Api\Counselor;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceMonitoringResource;
use App\Services\AttendanceMonitoringService;
use Illuminate\Http\Request;

class CounselorAttendanceMonitoringController extends Controller
{
    public function __construct(
        private AttendanceMonitoringService $attendanceMonitoringService
    ) {}

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['search', 'classroom', 'major', 'limit']);
            
            $list = $this->attendanceMonitoringService->getMonitoringData($filters);
            $recap = $this->attendanceMonitoringService->getRecap($filters);

            return response()->json([
                'success' => true,
                'message' => 'Data monitoring berhasil diambil',
                'data' => [
                    'recap' => $recap,
                    'list' => [
                        'data' => AttendanceMonitoringResource::collection($list->items()),
                        'meta' => [
                            'current_page' => $list->currentPage(),
                            'per_page' => $list->perPage(),
                            'total' => $list->total(),
                            'last_page' => $list->lastPage(),
                        ]
                    ]
                ]
            ]);

        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }

    public function syncData()
    {
        try {
            $this->attendanceMonitoringService->syncLatestData();
            return ResponseHelper::success(null, 'Data berhasil disinkronisasi');
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }
} 