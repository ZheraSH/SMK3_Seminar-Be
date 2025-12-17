<?php

namespace App\Services;

use Carbon\Carbon;
use App\Enums\DayEnum;
use App\Contracts\Interfaces\AttendanceInterface;
use App\Contracts\Interfaces\LessonScheduleInterface;
use App\Contracts\Interfaces\EmployeeInterface;

class HomeroomTeacherDashboardService
{
    public function __construct(
        private AttendanceInterface $attendanceRepo,
        private LessonScheduleInterface $scheduleRepo,
        private EmployeeInterface $employeeRepo
    ) {}

    public function getDashboard(string $employeeId): array
    {
        $today = Carbon::today()->toDateString();

        $dayIndo = strtolower(Carbon::now()->locale('id')->dayName);

        $day = DayEnum::translate($dayIndo);

        $teacher = $this->employeeRepo->show($employeeId);

        return [
            'teacher' => [
                'id'   => $teacher->id,
                'name' => $teacher->user->name ?? null,
            ],

            'weekly_attendance' => $this->attendanceRepo
                ->getSummaryByTeacher($teacher->id),

            'today_schedules' => $this->scheduleRepo
                ->getTodayByTeacher($teacher->id, $day),

            'today_attendance' => $this->attendanceRepo
                ->getTodayAttendanceByTeacher($teacher->id, $today),
        ];
    }
}
