<?php

namespace App\Http\Controllers\Api\Homeroom_teacher;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use App\Services\HomeroomTeacherDashboardService;
use App\Contracts\Interfaces\EmployeeInterface;
use Illuminate\Http\JsonResponse;

class HomeroomTeacherDashboardController extends Controller
{
    public function __construct(
        private HomeroomTeacherDashboardService $dashboardService,
        private EmployeeInterface $employeeRepo
    ) {}

    public function index(): JsonResponse
    {
        try {
            $user = auth()->user();

            $employee = $this->employeeRepo->getByUserId($user->id);

            if (!$employee) {
                return ResponseHelper::success([
                    'teacher' => null,
                    'weekly_attendance' => [],
                    'today_schedules' => [],
                    'today_attendance' => [],
                ], 'Data wali kelas tidak ditemukan');
            }

            $data = $this->dashboardService->getDashboard($employee->id);

            return ResponseHelper::success(
                $data,
                'Dashboard wali kelas berhasil dimuat'
            );

        } catch (\Throwable $th) {
            return ResponseHelper::error(
                $th->getMessage(),
                $th->getCode() ?: 400
            );
        }
    }
}
