<?php

namespace App\Services\Counselor;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Contracts\Repositories\AttendanceRfidRepository;
use Illuminate\Support\Collection;

class CounselorDashboardService
{
    private AttendanceRfidRepository $attendanceRfidRepository;

    public function __construct(AttendanceRfidRepository $attendanceRfidRepository)
    {
        $this->attendanceRfidRepository = $attendanceRfidRepository;
    }

    public function getAttendanceCounts(User $user, Request $request): array
    {
        return $this->attendanceRfidRepository->countTotalStatusToday(Carbon::today()->toDateString());
    }

    public function getHighAlphaStudents(User $user, Request $request): Collection
    {
        $limit = $request->input('limit', 10);
        $threshold = 3;
        return $this->attendanceRfidRepository->getStudentsWithHighAlpha($threshold, (int) $limit);
    }
}
