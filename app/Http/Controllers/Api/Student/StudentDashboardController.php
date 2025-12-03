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

            if (!$user->student) {
                return ResponseHelper::success(null, 'Tidak ada data siswa', 200);
            }

            $studentId = $user->student->id;

            $data = $this->service->getDashboardData($studentId);

            return ResponseHelper::success($data, 'Dashboard berhasil dimuat', 200);

        } catch (\Throwable $th) {
            return ResponseHelper::error(
                'Gagal memuat dashboard: ' . $th->getMessage(),
                500,
                null
            );
        }   
    }

        public function approve($id)
    {
        $permission = $this->permissionRepo->show($id);

        $this->service->approvePermission($permission);

        return ResponseHelper::success("Berhasil Approve Izin");
    }
}
