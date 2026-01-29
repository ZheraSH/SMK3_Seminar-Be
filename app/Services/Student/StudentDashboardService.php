<?php

namespace App\Services\Student;

use App\Contracts\Repositories\AttendancePermissionRepository;
use App\Contracts\Repositories\AttendanceRepository;
use App\Models\User;
use Illuminate\Http\Request;

class StudentDashboardService
{
    private AttendanceRepository $attendanceRepository;
    private AttendancePermissionRepository $attendancePermissionRepository;

    public function __construct(AttendanceRepository $attendanceRepository, AttendancePermissionRepository $attendancePermissionRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
        $this->attendancePermissionRepository = $attendancePermissionRepository;
    }

    public function getAttendanceSummary(User $user, Request $request): array
    {
        $studentId = $user->student->id;
        $attendance = $this->attendanceRepository->getStudentSummary($studentId);
        $izin = $this->attendancePermissionRepository->countApprovedByStudent($studentId);

        return [
            'hadir' => (int) $attendance['hadir'],
            'telat' => (int) $attendance['telat'],
            'alpha' => (int) $attendance['alpha'],
            'izin'  => $izin,
        ];
    }

    public function getMonthlyAttendance(User $user, Request $request): array
    {
        $studentId = $user->student->id;
        $year = $request->input('year', now()->year);

        return $this->attendanceRepository->getStudentMonthlyStatistic($studentId, (int) $year);
    }
}
