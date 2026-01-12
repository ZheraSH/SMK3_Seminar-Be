<?php

namespace App\Services\Homeroom_Teacher;

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

    public function getDailyStats(User $teacher): array
    {

        return $this->homeroomTeacherService->getDailySummary($teacher, Carbon::now()->toDateString());
    }

    public function getTodaysRfidLog(User $teacher): \Illuminate\Database\Eloquent\Collection
    {
        $classroom = $this->homeroomTeacherService->getTeacherClassroom($teacher);

        if (!$classroom) {
            throw new \Exception('Anda tidak memiliki kelas sebagai wali kelas', 404);
        }

        return $this->attendanceRepository->getRFIDLogByClassroom($classroom['id'], Carbon::now()->toDateString());
    }
}
