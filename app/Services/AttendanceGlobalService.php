<?php

namespace App\Services;

use App\Contracts\Interfaces\AttendanceGlobalInterface;

class AttendanceGlobalService
{
    protected AttendanceGlobalInterface $repo;

    public function __construct(AttendanceGlobalInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Mengambil statistik global absensi
     */
    public function getStatistics(array $filters): array
    {
        $stats = $this->repo->getGlobalStats($filters);
        $total = array_sum($stats);

        $summary = [
            'total'   => $total,
            'present' => $stats['present'] ?? 0,
            'absent'  => ($stats['leave'] ?? 0) + ($stats['sick'] ?? 0) + ($stats['alpha'] ?? 0),
            'avg'     => $total > 0 
                            ? round(($stats['present'] ?? 0) / $total * 100, 2) 
                            : 0,
        ];

        $paginated = $this->repo->getPaginated($filters);

        return [
            'summary'       => $summary,
            'proportion'    => $stats,
            'monthly_trend' => $this->repo->getMonthlyTrend($filters),
            'logs'          => $paginated,
            'pagination'    => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ];
    }
}
