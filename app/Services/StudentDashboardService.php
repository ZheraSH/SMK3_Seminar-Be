<?php

namespace App\Services;

use Carbon\Carbon;
use App\Contracts\Interfaces\StudentInterface;
use App\Contracts\Interfaces\AttendanceInterface;
use App\Contracts\Interfaces\LessonScheduleInterface;
use App\Contracts\Interfaces\AttendancePermissionInterface;
use App\Contracts\Interfaces\ClassroomStudentsInterface;
use App\Models\Student;

class StudentDashboardService
{
    public function __construct(
        private StudentInterface $studentRepo,
        private AttendanceInterface $attendanceRepo,
        private LessonScheduleInterface $scheduleRepo,
        private AttendancePermissionInterface $permissionRepo,
        private ClassroomStudentsInterface $classroomStudentRepository
    ) {}

    public function getDashboardData(string $studentId): array
    {
        $student = $this->studentRepo->findWithClassroom($studentId);
        $header = $this->getHeader($student);
        $attendanceSummary = $this->getAttendanceSummary($studentId);
        $todaySchedules = $this->getTodaySchedules($studentId);
        $latestPermissions = $this->getLatestPermissions($studentId);

        return [
            'header' => $header,
            'attendance' => ['summary' => $attendanceSummary],
            'today_schedules' => $todaySchedules,
            'latest_permissions' => $latestPermissions,
        ];
    }

    private function getHeader(Student $student): array
    {
        $classroomName = $this->getClassroomName($student);

        return [
            'name' => $student->user->name ?? '-',
            'classroom' => $classroomName,
            'avatar' => $student->user->profile_picture ?? null,
        ];
    }

    private function getClassroomName(Student $student): string
    {
        if ($student->classroom && $student->classroom->name) {
            return $student->classroom->name;
        }

        $classroomStudent = $this->classroomStudentRepository
            ->getLatestByStudent($student->id);

        if ($classroomStudent && $classroomStudent->classroom) {
            $level = $classroomStudent->classroom->levelClass->name ?? '';
            $major = $classroomStudent->classroom->major->name ?? '';
            return trim("$level $major") ?: '-';
        }

        return '-';
    }

    private function getAttendanceSummary(string $studentId): array
    {
        $attendanceSummary = $this->attendanceRepo->getSummary($studentId);

        return [
            'present' => $attendanceSummary['present'] ?? 18,
            'sick_leave' => ($attendanceSummary['sick'] ?? 18) + ($attendanceSummary['leave'] ?? 0),
            'late' => $attendanceSummary['late'] ?? 18,
            'alpha' => $attendanceSummary['alpha'] ?? 18,
        ];
    }

    private function getTodaySchedules(string $studentId): array
    {
        $dayName = strtolower(Carbon::now()->englishDayOfWeek);

        return $this->scheduleRepo
            ->getByStudentAndDay($studentId, $dayName)
            ->map(function ($item, $index) {
                $hour = $this->formatLessonHour($item->lessonHour ?? null, $index);

                return [
                    'subject' => $item->subject->name ?? '-',
                    'teacher' => $item->employee->user->name ?? '-',
                    'hour' => $hour,
                    'classroom' => $item->classroom->name ?? '-',
                ];
            })
            ->values()
            ->toArray();
    }

    private function formatLessonHour($lessonHour, int $index): string
    {
        if ($lessonHour && isset($lessonHour->sequence)) {
            $sequence = (int)$lessonHour->sequence;
            return "Jam Ke {$sequence} - " . ($sequence + 1);
        }

        $jamKe = $index + 1;
        return "Jam Ke {$jamKe} - " . ($jamKe + 1);
    }

    /**
     * FIXED SECTION
     * Konsisten gunakan formatPermission()
     */
    private function getLatestPermissions(string $studentId): array
{
    $permissions = $this->permissionRepo->getLatest($studentId);

    if ($permissions->isEmpty()) {
        return [];
    }

    return $permissions->map(
        fn($permission) => $this->formatPermission($permission)
    )->values()->toArray();
}

private function formatPermission($permission): array
{
    // Fix enum status → ambil string value
    $rawStatus = $permission->status instanceof \BackedEnum
        ? $permission->status->value
        : (string) $permission->status;

    $rawStatus = strtolower($rawStatus);

    $statusMap = [
        'approved' => 'Approve',
        'rejected' => 'Decline',
        'pending' => 'Waiting',
    ];

    $status = $statusMap[$rawStatus] ?? '-';
    $date = $permission->created_at?->format('d/m/Y') ?? '-';
    $reason = $this->truncateReason($permission->reason ?? '-');

    return [
        'Tanggal' => $date,
        'Alasan' => $reason,
        'Status' => $status,
    ];
}

    private function truncateReason(string $reason): string
    {
        return strlen($reason) > 50
            ? substr($reason, 0, 50) . '...'
            : $reason;
    }
}
