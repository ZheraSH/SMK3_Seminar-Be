<?php

namespace App\Http\Controllers\Api\Student;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\StudentDashboardService;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    public function __construct(
        private StudentDashboardService $service
    ) {}

    public function index(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->student) {
                return ResponseHelper::success(null, 'Tidak ada data siswa', 200);
            }

            $data = $this->service->getDashboardData($user->student->id);

            return ResponseHelper::success($data, 'Dashboard berhasil dimuat', 200);
        } catch (\Throwable $e) {
            return ResponseHelper::error(
                'Gagal memuat dashboard: ' . $e->getMessage(),
                500
            );
        }
    }
}
