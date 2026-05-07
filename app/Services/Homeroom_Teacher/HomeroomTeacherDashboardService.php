<?php

namespace App\Services\Homeroom_Teacher;

use App\Services\Homeroom_Teacher\HomeroomTeacherService;
use App\Contracts\Repositories\AttendanceRfidRepository;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HomeroomTeacherDashboardService
{
    private HomeroomTeacherService $homeroomTeacherService;
    private AttendanceRfidRepository $attendanceRfidRepository;

    public function __construct(HomeroomTeacherService $homeroomTeacherService, AttendanceRfidRepository $attendanceRfidRepository) 
    {
        $this->homeroomTeacherService = $homeroomTeacherService;
        $this->attendanceRfidRepository = $attendanceRfidRepository;
    }

    public function getDailyStats(User $user): array
    {
        return $this->homeroomTeacherService->getDailySummary($user, Carbon::now()->toDateString());
    }

    public function getTodaysRfidLog(User $user): Collection
    {
        $classroom = $this->homeroomTeacherService->getTeacherClassroom($user);

        if (!$classroom) {
            throw new \Exception('Anda tidak memiliki kelas sebagai wali kelas', 404);
        }

        return $this->attendanceRfidRepository->getByClassroomAndDate($classroom['id'], Carbon::now()->toDateString());
    }
}