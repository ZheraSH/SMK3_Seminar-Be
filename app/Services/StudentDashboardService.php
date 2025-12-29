<?php

namespace App\Services;

use App\Contracts\Interfaces\StudentInterface;
use App\Contracts\Interfaces\AttendanceInterface;
use App\Contracts\Interfaces\AttendancePermissionInterface;
use App\Contracts\Interfaces\ClassroomStudentsInterface;
use App\Enums\AttendanceStatusEnum;
use App\Enums\PermissionStatusEnum;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StudentDashboardService
{
    public function __construct(
        private StudentInterface $studentRepo,
        private AttendanceInterface $attendanceRepo,
        private AttendancePermissionInterface $permissionRepo,
        private ClassroomStudentsInterface $classroomStudentRepo,
        private StudentLessonScheduleService $scheduleService
    ) {}

    public function getDashboardData(string $studentId): array
    {
        $student = $this->studentRepo->showWithActiveClassroom($studentId);

        return [
            'header' => $this->getHeader($student),
            'attendance' => [
                'summary' => $this->getAttendanceSummary($studentId),
            ],
            'today_schedules' => $this->getTodaySchedules($student),
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
        $cls = $student->classroomStudents
            ->where('status', 'ACTIVE')
            ->first();

        if (!$cls || !$cls->classroom) return '-';

        $level = $cls->classroom->levelClass->name ?? '';
        $major = $cls->classroom->major->name ?? '';
        $name  = $cls->classroom->name ?? '';

        $map = [
            'Pengembangan Perangkat Lunak & Game' => 'PPLG',
            'Teknik Jaringan Komputer & Telekomunikasi' => 'TJKT',
            'Desain Komunikasi Visual' => 'DKV',
        ];

        preg_match('/(\d+)/', $name, $match);
        $rombel = $match[1] ?? '';

        return trim($level . ' ' . ($map[$major] ?? $major) . ' ' . $rombel) ?: '-';
    }

    private function getAttendanceSummary(string $studentId): array
    {
        $row = DB::table('attendances')
            ->where('student_id', $studentId)
            ->selectRaw("
                SUM(status = ?) AS present,
                SUM(status = ?) AS late,
                SUM(status = ?) AS alpha,
                SUM(status = ?) AS sick
            ", [
                AttendanceStatusEnum::PRESENT->value,
                AttendanceStatusEnum::LATE->value,
                AttendanceStatusEnum::ALPHA->value,
                AttendanceStatusEnum::SICK->value,
            ])
            ->first();

        $approvedPermission = DB::table('attendance_permissions')
            ->where('student_id', $studentId)
            ->where('status', PermissionStatusEnum::APPROVED->value)
            ->count();

        return [
            'present' => (int) ($row->present ?? 0),
            'late' => (int) ($row->late ?? 0),
            'alpha' => (int) ($row->alpha ?? 0),
            'sick' => (int) ($row->sick ?? 0) + $approvedPermission,
        ];
    }

    private function getTodaySchedules(Student $student): array
    {
        $day = strtolower(Carbon::now()->englishDayOfWeek);

        $classroom = $student->classroomStudents
            ->where('status', 'ACTIVE')
            ->first();

        if (!$classroom) return [];

        $schedules = $this->scheduleService
            ->getSchedule($student->id, $day);

        $classroomName = $this->getClassroomName($student);

        return array_map(function ($item) use ($classroomName) {
            $item['classroom'] = $classroomName;
            return $item;
        }, $schedules);
    }

    private function getLatestPermissions(string $studentId): array
    {
        $permissions = $this->permissionRepo->getLatest($studentId);

        if ($permissions->isEmpty()) return [];

        return $permissions->map(function ($p) {
            $status = PermissionStatusEnum::tryFrom(
                strtolower($p->status instanceof \BackedEnum ? $p->status->value : $p->status)
            );

            return [
                'Tanggal' => $p->created_at?->format('d/m/Y') ?? '-',
                'Alasan' => strlen($p->reason ?? '') > 50
                    ? substr($p->reason, 0, 50) . '...'
                    : ($p->reason ?? '-'),
                'Status' => $status?->label() ?? 'Unknown',
            ];
        })->values()->toArray();
    }
}
