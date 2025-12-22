<?php

namespace App\Services\Student;

use App\Contracts\Repositories\AttendancePermissionRepository;
use App\Contracts\Repositories\AttendanceRepository;

class StudentDashboardService
{
    private AttendanceRepository $attendanceRepository;
    private AttendancePermissionRepository $attendancePermissionRepository;
    public function __construct(AttendanceRepository $attendanceRepository, AttendancePermissionRepository $attendancePermissionRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
        $this->attendancePermissionRepository = $attendancePermissionRepository;
    }

    public function getAttendanceSummary(string $studentId): array
    {
        $attendance = $this->attendanceRepository->getStudentSummary($studentId);
        $izin = $this->attendancePermissionRepository->countApprovedByStudent($studentId);

        return [
            'hadir' => (int) $attendance['hadir'],
            'telat' => (int) $attendance['telat'],
            'alpha' => (int) $attendance['alpha'],
            'izin'  => $izin,
        ];
    }

    public function getMonthlyAttendance(string $studentId, ?int $year = null): array
    {
        $year ??= now()->year;

        return $this->attendanceRepository->getStudentMonthlyStatistic($studentId, $year);
    }
}