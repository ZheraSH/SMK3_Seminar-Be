<?php

namespace App\Services\Student;

use App\Contracts\Repositories\AttendanceRepository;
use App\Contracts\Repositories\AttendanceRfidRepository;
use App\Contracts\Repositories\AttendancePermissionRepository;
use App\Models\User;
use Illuminate\Http\Request;

class StudentDashboardService
{
    private AttendanceRepository $attendanceRepository;
    private AttendanceRfidRepository $attendanceRfidRepository;
    private AttendancePermissionRepository $attendancePermissionRepository;

    public function __construct(AttendanceRepository $attendanceRepository, AttendanceRfidRepository $attendanceRfidRepository, AttendancePermissionRepository $attendancePermissionRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
        $this->attendanceRfidRepository = $attendanceRfidRepository;
        $this->attendancePermissionRepository = $attendancePermissionRepository;
    }

    public function getAttendanceSummary(User $user, Request $request): array
    {
        $studentId = $user->student->id;
        $year = $request->input('year', now()->year);
        $attendance = $this->attendanceRepository->getStudentYearlySummary($studentId, (int) $year);

        return [
            'hadir' => (int) ($attendance['hadir'] ?? 0),
            'sakit' => (int) ($attendance['sakit'] ?? 0),
            'izin'  => (int) ($attendance['izin'] ?? 0),
            'alpha' => (int) ($attendance['alpha'] ?? 0),
        ];
    }

    public function getMonthlyAttendance(User $user, Request $request): array
    {
        $studentId = $user->student->id;
        $year = $request->input('year', now()->year);

        return $this->attendanceRfidRepository->getStudentMonthlyStatistic($studentId, (int) $year);
    }
}
