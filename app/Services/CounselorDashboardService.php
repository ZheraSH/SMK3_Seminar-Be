<?php

namespace App\Services;

use Carbon\Carbon;
use App\Contracts\Interfaces\AttendanceInterface;
use App\Contracts\Interfaces\ClassroomStudentsInterface;

class CounselorDashboardService 
{
    public function __construct(
        private AttendanceInterface $attendanceRepo,
        private ClassroomStudentsInterface $classroomStudentRepo,
    ) {}

    /**
     * Main Dashboard
     */
    public function getDashboardData(array $filters = []): array
    {
        return [
            'recap_today' => $this->getTodayRecap($filters),
            'top_alpha_students' => $this->getTopAlphaStudents($filters),
        ];
    }

    /**
     * Recap Kehadiran Hari Ini
     */
    public function getTodayRecap(array $filters = []): array
    {
        $today = Carbon::today()->format('Y-m-d');

        $recap = $this->attendanceRepo->getDailyRecap($today, $filters);
        $totalStudents = $this->attendanceRepo->totalStudents($filters);

        $pct = fn($v) => $totalStudents ? round(($v / $totalStudents) * 100) : 0;

        return [
            'date' => $today,
            'total_students' => $totalStudents,

            'present' => [
                'count' => $recap['present'] ?? 0,
                'percentage' => $pct($recap['present'] ?? 0),
            ],

            'izin' => [
                'count' => $recap['izin'] ?? 0,
                'percentage' => $pct($recap['izin'] ?? 0),
            ],

            'sakit' => [
                'count' => $recap['sakit'] ?? 0,
                'percentage' => $pct($recap['sakit'] ?? 0),
            ],
            
            'alpha' => [
                'count' => $recap['alpha'] ?? 0,
                'percentage' => $pct($recap['alpha'] ?? 0),
            ],
        ];
    }

    public function getTopAlphaStudents(array $filters = [], int $limit = 5): array
    {
        $filters['start_date'] = Carbon::now()->startOfMonth()->format('Y-m-d');
        $filters['end_date'] = Carbon::today()->format('Y-m-d');

        $students = $this->attendanceRepo->getTopAlphaStudents(
            Carbon::today()->format('Y-m-d'),
            $filters,
            $limit
        );

        return $students->map(function ($s, $i) {
            return [
                'rank' => $i + 1,
                'name' => $s->user->name ?? '-',
                'classroom' => $s->classroomStudents->first()->classroom->name ?? '-',
                'status' => 'Alpha',
                'total' => $s->alpha_total ?? 0,
            ];
        })->toArray();  
    }
}
