<?php

namespace App\Services\Homeroom_Teacher;

use App\Contracts\Repositories\AttendanceRepository;
use App\Contracts\Repositories\AttendancePermissionRepository;
use App\Contracts\Repositories\Operator\ClassroomStudentsRepository;
use App\Contracts\Repositories\Operator\ClassroomRepository;
use App\Enums\AttendanceStatusEnum;
use App\Enums\RoleEnum;
use App\Models\User;
use Carbon\Carbon;

class HomeroomTeacherService
{
    protected ClassroomRepository $classroomRepository;
    protected ClassroomStudentsRepository $classroomStudentsRepository;
    protected AttendanceRepository $attendanceRepository;
    protected AttendancePermissionRepository $attendancePermissionRepository;

    public function __construct(ClassroomRepository $classroomRepository, ClassroomStudentsRepository $classroomStudentsRepository, AttendanceRepository $attendanceRepository, AttendancePermissionRepository $attendancePermissionRepository)
    {
        $this->classroomRepository = $classroomRepository;
        $this->classroomStudentsRepository = $classroomStudentsRepository;
        $this->attendanceRepository = $attendanceRepository;
        $this->attendancePermissionRepository = $attendancePermissionRepository;
    }

    public function getTeacherClassroom(User $teacher): ?array
    {
        if (!$this->isHomeroomTeacher($teacher)) {
            return null;
        }

        $teacher->loadMissing('employee');

        if (!$teacher->employee) {
            return null;
        }

        $classroom = $this->classroomRepository->getModel()
            ->where('homeroom_teacher_id', $teacher->employee->id)
            ->with(['major', 'levelClass', 'schoolYear'])
            ->first();

        if (!$classroom) {
            return null;
        }

        return [
            'id' => $classroom->id,
            'name' => $classroom->name,
            'major' => $classroom->major?->name,
            'level_class' => $classroom->levelClass?->name,
            'school_year' => $classroom->schoolYear?->name,
            'total_students' => $this->classroomStudentsRepository
                ->countActiveByClassroom($classroom->id),
        ];
    }

    public function getClassroomHeader(User $teacher): array
    {
        $classroom = $this->requireClassroom($teacher);

        return [
            'classroom_id' => $classroom['id'],
            'classroom_name' => $classroom['name'],
            'tahun_ajaran' => $classroom['school_year'],
            'total_students' => $classroom['total_students'],
        ];
    }

    public function getDailySummary(User $teacher, string $date): array
    {
        $classroom = $this->requireClassroom($teacher);

        if ($classroom['total_students'] === 0) {
            return $this->emptyDailySummary($classroom);
        }

        $attendances = $this->attendanceRepository
            ->getByClassroomAndDate($classroom['id'], $date)
            ->where('lesson_order', 1);

        $permissions = $this->getApprovedPermissions($classroom['id'], $date);

        $counters = $this->countAttendance(
            $classroom['id'],
            $date,
            $attendances,
            $permissions
        );

        $attended = $counters['present']
            + $counters['late']
            + $counters['sick']
            + $counters['permission'];

        return [
            'date' => $date,
            'day_name' => Carbon::parse($date)->translatedFormat('l'),
            'classroom_id' => $classroom['id'],
            'classroom_name' => $classroom['name'],
            'tahun_ajaran' => $classroom['school_year'],
            'total_students' => $classroom['total_students'],
            ...$counters,
            'percentage' => round(($attended / $classroom['total_students']) * 100, 2),
        ];
    }

    public function getWeeklyStatistics(User $teacher, string $startDate, string $endDate): array
    {
        $classroom = $this->requireClassroom($teacher);

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $daily = [];

        while ($start->lte($end)) {
            $date = $start->format('Y-m-d');

            $attendances = $this->attendanceRepository
                ->getByClassroomAndDate($classroom['id'], $date)
                ->where('lesson_order', 1);

            $permissions = $this->getApprovedPermissions($classroom['id'], $date);

            $count = $this->countAttendance(
                $classroom['id'],
                $date,
                $attendances,
                $permissions
            );

            $daily[] = [
                'date' => $date,
                'day_name' => $start->translatedFormat('l'),
                'day_short' => $start->translatedFormat('D'),
                'total_students' => $classroom['total_students'],
                'hadir' => $count['present'] + $count['late'],
                'izin' => $count['permission'],
                'alpha' => $count['alpha'],
                'telat' => $count['late'],
                'sakit' => $count['sick'],
            ];

            $start->addDay();
        }

        return [
            'classroom' => [
                'id' => $classroom['id'],
                'name' => $classroom['name'],
                'total_students' => $classroom['total_students'],
            ],
            'daily_data' => $daily,
        ];
    }

    public function getDailyAttendance(User $teacher, string $date, ?string $search = null, ?string $status = null, int $perPage = 10)
    {
        $classroom = $this->requireClassroom($teacher);

        $studentsPaginated = $this->classroomStudentsRepository
            ->getByClassroomForDailyAttendance($classroom['id'], $search, $perPage);

        $attendances = $this->attendanceRepository
            ->getByClassroomAndDate($classroom['id'], $date)
            ->where('lesson_order', 1);

        $permissions = $this->getApprovedPermissions($classroom['id'], $date);

        $students = $studentsPaginated->map(
            fn($cs) => $this->mapStudentAttendance($cs, $date, $attendances, $permissions)
        )->filter();

        if ($status) {
            $students = $students->filter(fn($student) => $student && $student['status'] === $status);
        }

        return [
            'students' => $students->values(),
            'pagination' => [
                'current_page' => $studentsPaginated->currentPage(),
                'per_page' => $studentsPaginated->perPage(),
                'total' => $studentsPaginated->total(),
                'last_page' => $studentsPaginated->lastPage(),
            ],
        ];
    }

    private function requireClassroom(User $teacher): array
    {
        $classroom = $this->getTeacherClassroom($teacher);

        if (!$classroom) {
            throw new \Exception('Anda tidak memiliki kelas sebagai wali kelas', 404);
        }

        return $classroom;
    }

    private function isHomeroomTeacher(User $user): bool
    {
        return $user->roles->contains(
            fn($role) => $role->name === RoleEnum::HOMEROOM_TEACHER->value
        );
    }

    private function countAttendance(string $classroomId, string $date, $attendances, $permissions): array
    {
        $counters = [
            'present' => 0,
            'late' => 0,
            'sick' => 0,
            'permission' => 0,
            'alpha' => 0,
        ];

        $students = \App\Models\ClassroomStudents::where('classroom_id', $classroomId)
            ->where('status', 'active')
            ->pluck('student_id');

        foreach ($students as $studentId) {
            if ($permissions->contains('student_id', $studentId)) {
                $counters['permission']++;
                continue;
            }

            $attendance = $attendances->firstWhere('student_id', $studentId);

            if (!$attendance) {
                $counters['alpha']++;
                continue;
            }

            match ($attendance->status) {
                AttendanceStatusEnum::PRESENT->value => $counters['present']++,
                AttendanceStatusEnum::LATE->value => $counters['late']++,
                AttendanceStatusEnum::SICK->value => $counters['sick']++,
                default => $counters['alpha']++,
            };
        }

        return $counters;
    }

    private function mapStudentAttendance($cs, string $date, $attendances, $permissions): ?array
    {
        if (!$cs->student || !$cs->student->user) {
            return null;
        }

        $studentId = $cs->student_id;

        $studentImage = $cs->student->image
            ? asset('storage/' . $cs->student->image)
            : null;

        if ($permissions->contains('student_id', $studentId)) {
            return [
                'student_image' => $studentImage,
                'student_name' => $cs->student->user->name,
                'nisn' => $cs->student->nisn,
                'status' => 'permission',
                'date' => $date,
            ];
        }

        $attendance = $attendances->firstWhere('student_id', $studentId);

        return [
            'student_image' => $studentImage,
            'student_name' => $cs->student->user->name,
            'nisn' => $cs->student->nisn,
            'status' => $attendance?->status ?? 'alpha',
            'date' => $date,
        ];
    }

    private function getApprovedPermissions(string $classroomId, string $date)
    {
        $date = Carbon::parse($date);

        return \App\Models\AttendancePermission::where('status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->whereHas(
                'student.classroomStudents',
                fn($q) =>
                $q->where('classroom_id', $classroomId)
            )
            ->get();
    }

    public function generateAttendanceRecap(User $teacher, ?string $date = null): array
    {
        $date = $date ?? Carbon::now()->format('Y-m-d');
        $classroom = $this->requireClassroom($teacher);

        $dailyData = $this->getDailyAttendance($teacher, $date);

        $attendances = $this->attendanceRepository
            ->getByClassroomAndDate($classroom['id'], $date)
            ->where('lesson_order', 1);

        $permissions = $this->getApprovedPermissions($classroom['id'], $date);

        $counters = $this->countAttendance(
            $classroom['id'],
            $date,
            $attendances,
            $permissions
        );

        return [
            'date' => $date,
            'day_name' => Carbon::parse($date)->translatedFormat('l'),
            'classroom' => [
                'id' => $classroom['id'],
                'name' => $classroom['name'],
            ],
            'tahun_ajaran' => $classroom['school_year'],
            'total_students' => $classroom['total_students'],
            'attendance_summary' => $counters,
            'students' => $dailyData['students'],
        ];
    }

    private function emptyDailySummary(array $classroom): array
    {
        return [
            'classroom_id' => $classroom['id'],
            'classroom_name' => $classroom['name'],
            'tahun_ajaran' => $classroom['school_year'],
            'total_students' => 0,
        ];
    }
}
