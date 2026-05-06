<?php

namespace App\Services\Student;

use App\Contracts\Repositories\AttendancePermissionRepository;
use App\Contracts\Repositories\AttendanceRfidRepository;
use App\Models\User;
use Illuminate\Http\Request;

class StudentDashboardService
{
    private AttendanceRfidRepository $attendanceRfidRepository;
    private AttendancePermissionRepository $attendancePermissionRepository;

    public function __construct(AttendanceRfidRepository $attendanceRfidRepository, AttendancePermissionRepository $attendancePermissionRepository)
    {
        $this->attendanceRfidRepository = $attendanceRfidRepository;
        $this->attendancePermissionRepository = $attendancePermissionRepository;
    }

    public function getAttendanceSummary(User $user, Request $request): array
    {
        $studentId = $user->student->id;
        $attendance = $this->attendanceRfidRepository->getStudentSummary($studentId);
        $izin = $this->attendancePermissionRepository->countApprovedByStudent($studentId);

        return [
            'hadir' => (int) ($attendance['hadir'] ?? 0),
            'telat' => (int) ($attendance['telat'] ?? 0),
            'alpha' => 0, // Alpha dihitung dari luar (hari sekolah - total record)
            'izin'  => $izin,
        ];
    }

    public function getMonthlyAttendance(User $user, Request $request): array
    {
        $studentId = $user->student->id;
        $year = $request->input('year', now()->year);

        return $this->attendanceRfidRepository->getStudentMonthlyStatistic($studentId, (int) $year);
    }
}
