<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use App\Enums\AttendanceStatusEnum;
use App\Contracts\Interfaces\{
    StudentInterface,
    AttendanceInterface,
    LessonScheduleInterface,
    AttendancePermissionInterface,
    ClassroomStudentsInterface
};
use App\Enums\PermissionStatusEnum;

class StudentDashboardService
{
    public function __construct(
        private StudentInterface $studentRepo,
        private AttendanceInterface $attendanceRepo,
        private LessonScheduleInterface $scheduleRepo,
        private AttendancePermissionInterface $permissionRepo,
        private ClassroomStudentsInterface $classroomStudentRepo,
        private StudentLessonScheduleService $studentScheduleService
    ) {}


    public function getDashboardData(string $studentId): array
    {
        $student = $this->studentRepo->findWithClassroom($studentId);

        return [
            'header' => $this->getHeader($student),
            'attendance' => [
                'summary' => $this->getAttendanceSummary($studentId),
            ],
            'today_schedules' => $this->getTodaySchedules($studentId),
            'latest_permissions' => $this->getLatestPermissions($studentId),
        ];
    }

    private function getHeader(Student $student): array
    {
        return [
            'name' => $student->user->name ?? '-',
            'classroom' => $this->getClassroomName($student),
            'avatar' => $student->user->profile_picture ?? null,
        ];
    }

    private function getClassroomName(Student $student): string
    {
        if ($student->classroom?->name) {
            return $student->classroom->name;
        }

        $cls = $this->classroomStudentRepo->getLatestByStudent($student->id);
        if (!$cls || !$cls->classroom) return '-';

        $level = $cls->classroom->levelClass->name ?? '';
        $major = $cls->classroom->major->name ?? '';
        $name  = $cls->classroom->name;

        $map = [
            'Pengembangan Perangkat Lunak & Game' => 'PPLG',
            'Teknik Jaringan Komputer & Telekomunikasi' => 'TJKT',
            'Desain Komunikasi Visual' => 'DKV',
    ];

    $shortMajor = $map[$major] ?? $major;

    preg_match('/(\d+)/', $name, $match);
    $rombel = $match[1] ?? '';

    return trim("$level $shortMajor $rombel") ?: '-';
    }

  private function getAttendanceSummary(string $studentId): array
{
    $presentVal = AttendanceStatusEnum::PRESENT->value;
    $lateVal    = AttendanceStatusEnum::LATE->value;
    $alphaVal   = AttendanceStatusEnum::ALPHA->value;
    $sickVal    = AttendanceStatusEnum::SICK->value;

    $row = DB::table('attendances as a')
        ->leftJoin('attendance_permissions as p', function ($join) {
            $join->on('p.student_id', '=', 'a.student_id')
                 ->whereRaw('a.date BETWEEN p.start_date AND p.end_date');
        })
        ->where('a.student_id', $studentId)
        ->selectRaw("
            SUM(CASE WHEN a.status = ? THEN 1 ELSE 0 END) as present,
            SUM(CASE WHEN a.status = ? THEN 1 ELSE 0 END) as late,
            SUM(CASE WHEN a.status = ? THEN 1 ELSE 0 END) as alpha,
            SUM(CASE WHEN a.status = ? THEN 1 ELSE 0 END) as sick_absen
        ", [$presentVal, $lateVal, $alphaVal, $sickVal])
        ->first();

    $approvedPermissionsCount = DB::table('attendance_permissions')
        ->where('student_id', $studentId)
        ->where('status', PermissionStatusEnum::APPROVED->value)
        ->count();

    return [
        'present' => (int) $row->present,
        'late'    => (int) $row->late,
        'alpha'   => (int) $row->alpha,
        'sick'    => (int) $row->sick_absen + (int) $approvedPermissionsCount,
    ];
}



    private function getTodaySchedules(string $studentId): array
    {
        $day = strtolower(Carbon::now()->englishDayOfWeek);

        $student = $this->studentRepo->findWithClassroom($studentId);

        $classroomName = $this->getClassroomName($student);

        $schedules = $this->studentScheduleService->getSchedule($studentId, $day);

        return array_map(function ($item) use ($classroomName) {
            $item['classroom'] = $classroomName;
            return $item;
        }, $schedules);
    }


    private function formatLessonHour(?int $seq, int $i): string
    {
        $start = $seq ?: ($i + 1);
        return "Jam Ke {$start} - " . ($start + 1);
    }

    private function getLatestPermissions(string $studentId): array
    {
        $perms = $this->permissionRepo->getLatest($studentId);
        if ($perms->isEmpty()) return [];

        return $perms->map(fn($p) => $this->formatPermission($p))
            ->values()->toArray();
    }

    private function formatPermission($p): array
    {
        $statusValue = strtolower($p->status instanceof \BackedEnum ? $p->status->value : $p->status);

        $statusEnum = PermissionStatusEnum::tryFrom($statusValue);

        return [
            'Tanggal' => $p->created_at?->format('d/m/Y') ?? '-',
            'Alasan' => $this->limit($p->reason ?? '-'),
            'Status' => $statusEnum?->label() ?? 'Unknown', 
        ];
    }

    private function limit(string $txt): string
    {
        return strlen($txt) > 50 ? substr($txt, 0, 50) . '...' : $txt;
    }
}
