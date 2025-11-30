<?php

namespace App\Services;

use App\Contracts\Interfaces\StudentInterface;
use App\Contracts\Interfaces\EmployeeInterface;
use App\Contracts\Interfaces\ClassroomInterface;
use App\Contracts\Interfaces\AttendanceInterface;
use App\Contracts\Interfaces\RfidInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OperatorDashboardService
{
    private StudentInterface $studentRepository;
    private EmployeeInterface $employeeRepository;
    private ClassroomInterface $classroomRepository;
    private AttendanceInterface $attendanceRepository;
    private RfidInterface $rfidRepository;

    public function __construct(
        StudentInterface $studentRepository,
        EmployeeInterface $employeeRepository,
        ClassroomInterface $classroomRepository,
        AttendanceInterface $attendanceRepository,
        RfidInterface $rfidRepository
    ) {
        $this->studentRepository = $studentRepository;
        $this->employeeRepository = $employeeRepository;
        $this->classroomRepository = $classroomRepository;
        $this->attendanceRepository = $attendanceRepository;
        $this->rfidRepository = $rfidRepository;
    }

    public function getMaster(): array
    {
        try {
            $totalStudents = $this->studentRepository->countActiveStudents();
            $totalTeachers = $this->employeeRepository->countByRoles(['teacher', 'bk', 'homeroom_teacher']);
            $totalStaff = $this->employeeRepository->countByRoles(['staff_tu']);
            $totalClassrooms = $this->classroomRepository->count();
            
            // Hitung persentase kehadiran hari ini
            $today = Carbon::now()->format('Y-m-d');
            $todayAttendance = $this->attendanceRepository->getByDate($today);
            
            $presentToday = $todayAttendance->where('status', 'present')->count();
            $attendancePercentage = $totalStudents > 0 ? round(($presentToday / $totalStudents) * 100, 2) : 0;

            return [
                'total_students' => $totalStudents,
                'total_teachers' => $totalTeachers,
                'total_staff' => $totalStaff,
                'total_classrooms' => $totalClassrooms,
                'attendance_percentage_today' => $attendancePercentage,
                'present_today' => $presentToday,
                'date' => $today
            ];
        } catch (\Exception $e) {
            throw new \Exception("Failed to get counters: " . $e->getMessage());
        }
    }

    public function getRfidTap(): array
    {
        try {
            // Query attendances table for recent RFID tap activities
            $activities = DB::table('attendances')
                ->select(
                    'attendances.id',
                    'attendances.date',
                    'attendances.checkin_time',
                    'attendances.checkout_time',
                    'attendances.status',
                    'attendances.proof',
                    'attendances.created_at',
                    'students.id as student_id',
                    'users.name as student_name',
                    'rfids.rfid as rfid_number'
                )
                ->join('students', 'attendances.student_id', '=', 'students.id')
                ->join('users', 'students.user_id', '=', 'users.id')
                ->leftJoin('rfids', 'attendances.rfid_id', '=', 'rfids.id')
                ->whereNotNull('attendances.rfid_id')
                ->whereNull('attendances.deleted_at')
                ->whereNull('students.deleted_at')
                ->orderBy('attendances.created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($tap) {
                    // Determine tap type based on checkin/checkout times
                    $tapType = 'checkin';
                    $tapTime = $tap->checkin_time;
                    
                    if ($tap->checkout_time) {
                        $tapType = 'checkout';
                        $tapTime = $tap->checkout_time;
                    }
                    
                    return [
                        'id' => $tap->id,
                        'rfid_number' => $tap->rfid_number ?? 'N/A',
                        'user_type' => 'student',
                        'user_name' => $tap->student_name,
                        'tap_time' => $tapTime,
                        'tap_type' => $tapType,
                        'status' => $tap->status,
                        'date' => $tap->date
                    ];
                });
            
            return [
                'activities' => $activities,
                'total_activities' => $activities->count(),
                'last_updated' => now()->toISOString()
            ];
        } catch (\Exception $e) {
            throw new \Exception("Failed to get recent RFID taps: " . $e->getMessage());
        }
    }

    public function getStatistics(): array
    {
        try {
            $stats = $this->getWeeklyAttendanceStats(7);
            
            return [
                'weekly_stats' => $stats,
                'period' => [
                    'start_date' => collect($stats)->first()['date'] ?? null,
                    'end_date' => collect($stats)->last()['date'] ?? null,
                    'total_days' => count($stats)
                ],
                'summary' => $this->calculateWeeklySummary($stats)
            ];
        } catch (\Exception $e) {
            throw new \Exception("Failed to get statistics: " . $e->getMessage());
        }
    }

    private function getWeeklyAttendanceStats(int $days = 7): array
    {
        $stats = [];
        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Get attendance data for the last 7 days
        $attendanceData = DB::table('attendances')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present'),
                DB::raw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent'),
                DB::raw('SUM(CASE WHEN status = "sick" THEN 1 ELSE 0 END) as sick'),
                DB::raw('SUM(CASE WHEN status = "permission" THEN 1 ELSE 0 END) as permission'),
                DB::raw('SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'desc')
            ->get();

        // Format the response
        foreach ($attendanceData as $data) {
            $total = $data->total;
            $present = $data->present;
            $absent = $data->absent + $data->sick + $data->permission;
            $alpha = $total - ($present + $absent);

            $stats[] = [
                'date' => $data->date,
                'present' => $present,
                'absent' => $absent,
                'alpha' => max(0, $alpha),
                'total' => $total,
                'attendance_rate' => $total > 0 ? round(($present / $total) * 100, 2) : 0
            ];
        }

        // Fill missing dates with zero values
        $formattedStats = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $existingData = collect($stats)->firstWhere('date', $date);
            
            if ($existingData) {
                $formattedStats[] = $existingData;
            } else {
                $formattedStats[] = [
                    'date' => $date,
                    'present' => 0,
                    'absent' => 0,
                    'alpha' => 0,
                    'total' => 0,
                    'attendance_rate' => 0
                ];
            }
        }

        return $formattedStats;
    }

    private function calculateWeeklySummary(array $stats): array
    {
        $totalPresent = collect($stats)->sum('present');
        $totalAbsent = collect($stats)->sum('absent');
        $totalAlpha = collect($stats)->sum('alpha');
        $totalStudents = collect($stats)->sum('total');
        
        $averageAttendanceRate = $totalStudents > 0 ? 
            round(($totalPresent / $totalStudents) * 100, 2) : 0;

        return [
            'total_present' => $totalPresent,
            'total_absent' => $totalAbsent,
            'total_alpha' => $totalAlpha,
            'average_attendance_rate' => $averageAttendanceRate,
            'best_day' => collect($stats)->sortByDesc('attendance_rate')->first(),
            'worst_day' => collect($stats)->sortBy('attendance_rate')->first()
        ];
    }
}