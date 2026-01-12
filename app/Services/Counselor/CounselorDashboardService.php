<?php

namespace App\Services\Counselor;

use Carbon\Carbon;
use App\Contracts\Repositories\AttendanceRepository;

class CounselorDashboardService
{
    private AttendanceRepository $attendanceRepository;

    public function __construct(AttendanceRepository $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
    }

    public function getAttendanceCounts(): array
    {
        return $this->attendanceRepository->countTotalStatusToday(Carbon::today()->toDateString());
    }

    public function getHighAlphaStudents(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        $threshold = 3;
        return $this->attendanceRepository->getStudentsWithHighAlpha($threshold, $limit);
    }
}
