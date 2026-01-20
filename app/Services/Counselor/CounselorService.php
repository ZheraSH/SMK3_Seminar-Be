<?php

namespace App\Services\Counselor;

use App\Contracts\Repositories\AttendanceRepository;

class CounselorService
{
    private AttendanceRepository $attendanceRepository;

    public function __construct(AttendanceRepository $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
    }

    public function getGlobalAttendanceStats(): array
    {
        $data = $this->attendanceRepository->countTotalStatusGlobal();

        $total = $data['total'] ?: 1;

        return [
            'counts' => [
                'hadir' => (int) $data['hadir'],
                'terlambat' => (int) $data['terlambat'],
                'izin' => (int) $data['izin'],
                'alpha' => (int) $data['alpha'],
                'total' => (int) $data['total'],
            ],
            'percentages' => [
                'hadir' => round(($data['hadir'] / $total) * 100, 2),
                'terlambat' => round(($data['terlambat'] / $total) * 100, 2),
                'izin' => round(($data['izin'] / $total) * 100, 2),
                'alpha' => round(($data['alpha'] / $total) * 100, 2),
            ]
        ];
    }

    public function getMonthlyAttendanceStats(int $year): array
    {
        return $this->attendanceRepository->countTotalStatusMonthly($year);
    }
}
