<?php

namespace App\Services\Homeroom_Teacher;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\Homeroom_Teacher\HomeroomTeacherService;
use App\Contracts\Repositories\AttendanceRepository;
use Carbon\Carbon;

class HomeroomTeacherDashboardService
{
    private HomeroomTeacherService $homeroomTeacherService;
    private AttendanceRepository $attendanceRepository;

    public function __construct(
        HomeroomTeacherService $homeroomTeacherService,
        AttendanceRepository $attendanceRepository
    ) {
        $this->homeroomTeacherService = $homeroomTeacherService;
        $this->attendanceRepository = $attendanceRepository;
    }

    public function getDailyStats(User $user, Request $request): array
    {
        return $this->homeroomTeacherService->getDailySummary($user, Carbon::now()->toDateString());
    }

    public function getTodaysRfidLog(User $user, Request $request): \Illuminate\Database\Eloquent\Collection
    {
        $classroom = $this->homeroomTeacherService->getTeacherClassroom($user);

        if (!$classroom) {
            throw new \Exception('Anda tidak memiliki kelas sebagai wali kelas', 404);
        }

        return $this->attendanceRepository->getRFIDLogByClassroom($classroom['id'], Carbon::now()->toDateString());
    }
}
