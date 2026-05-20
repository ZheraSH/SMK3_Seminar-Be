<?php

namespace App\Services\Counselor;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Contracts\Repositories\AttendanceRfidRepository;
use App\Contracts\Repositories\AttendanceRepository;
use Illuminate\Support\Collection;

class CounselorDashboardService
{
    private AttendanceRfidRepository $attendanceRfidRepository;
    private AttendanceRepository $attendanceRepository;

    public function __construct(AttendanceRfidRepository $attendanceRfidRepository, AttendanceRepository $attendanceRepository)
    {
        $this->attendanceRfidRepository = $attendanceRfidRepository;
        $this->attendanceRepository = $attendanceRepository;
    }

    public function getAttendanceCounts(User $user, Request $request): array
    {
        return $this->attendanceRepository->countTotalStatusToday(Carbon::today()->toDateString());
    }

    public function getHighAlphaStudents(User $user, Request $request): Collection
    {
        $limit = $request->input('limit', 10);
        $threshold = 3;
        return $this->attendanceRepository->getStudentsWithHighAlpha($threshold, (int) $limit);
    }
}
