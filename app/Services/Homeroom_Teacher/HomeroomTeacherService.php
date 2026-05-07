<?php

namespace App\Services\Homeroom_Teacher;

use App\Contracts\Repositories\AttendanceRepository;
use App\Contracts\Repositories\AttendanceRfidRepository;
use App\Contracts\Repositories\Operator\ClassroomStudentsRepository;
use App\Contracts\Repositories\Operator\ClassroomRepository;
use App\Enums\AttendanceStatusEnum;
use App\Enums\RfidAttendanceStatusEnum;
use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class HomeroomTeacherService
{
    private ClassroomRepository $classroomRepository;
    private ClassroomStudentsRepository $classroomStudentsRepository;
    private AttendanceRfidRepository $attendanceRfidRepository;
    private AttendanceRepository $attendanceRepository;

    public function __construct(ClassroomRepository $classroomRepository, ClassroomStudentsRepository $classroomStudentsRepository, AttendanceRfidRepository $attendanceRfidRepository, AttendanceRepository $attendanceRepository)
    {
        $this->classroomRepository = $classroomRepository;
        $this->classroomStudentsRepository = $classroomStudentsRepository;
        $this->attendanceRfidRepository = $attendanceRfidRepository;
        $this->attendanceRepository = $attendanceRepository;
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

        $attendances = $this->attendanceRfidRepository->getByClassroomAndDate($classroom['id'], $date);
        $crossCheckAttendances = $this->attendanceRepository->getByClassroomAndDate($classroom['id'], $date);

        $counters = $this->countAttendance(
            $classroom['id'],
            $date,
            $attendances,
            $crossCheckAttendances
        );

        $maxCount = max(
            $counters['present'],
            $counters['sick'],
            $counters['permission'],
            $counters['alpha']
        );

        return [
            'date' => $date,
            'day_name' => Carbon::parse($date)->translatedFormat('l'),
            'classroom_id' => $classroom['id'],
            'classroom_name' => $classroom['name'],
            'tahun_ajaran' => $classroom['school_year'],
            'total_students' => $classroom['total_students'],
            ...$counters,
            'percentage' => $classroom['total_students'] > 0 
                ? round(($maxCount / $classroom['total_students']) * 100, 2) 
                : 0,
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
            $attendances = $this->attendanceRfidRepository->getByClassroomAndDate($classroom['id'], $date);
            $crossCheckAttendances = $this->attendanceRepository->getByClassroomAndDate($classroom['id'], $date);

            $count = $this->countAttendance(
                $classroom['id'],
                $date,
                $attendances,
                $crossCheckAttendances
            );

            $daily[] = [
                'date' => $date,
                'day_name' => $start->translatedFormat('l'),
                'day_short' => $start->translatedFormat('D'),
                'total_students' => $classroom['total_students'],
                'hadir' => $count['present'],
                'izin' => $count['permission'],
                'alpha' => $count['alpha'],
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

    public function getDailyAttendance(User $teacher, Request $request)
    {
        $classroom = $this->requireClassroom($teacher);
        $date = $request->input('date', now()->format('Y-m-d'));
        $search = $request->input('search');
        $status = $request->input('status');
        $perPage = $request->input('per_page', 10);

        $studentsPaginated = $this->classroomStudentsRepository
            ->getByClassroomForDailyAttendance($classroom['id'], $date, $search, $status, $perPage);

        $attendances = $this->attendanceRfidRepository->getByClassroomAndDate($classroom['id'], $date);
        $crossCheckAttendances = $this->attendanceRepository->getByClassroomAndDate($classroom['id'], $date);

        $students = $studentsPaginated->map(
            fn($cs) => $this->mapStudentAttendance($cs, $date, $attendances, $crossCheckAttendances)
        );

        return [
            'students' => $students->values(),
            'pagination' => $this->classroomStudentsRepository->formatPagination($studentsPaginated),
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

    private function countAttendance(string $classroomId, string $date, $attendances, $crossCheckAttendances): array
    {
        $counters = [
            'present' => 0,
            'sick' => 0,
            'permission' => 0,
            'alpha' => 0,
        ];

        $students = $this->classroomStudentsRepository->getActiveStudentIds($classroomId);

        foreach ($students as $studentId) {
            $rfidAttendance = $attendances->firstWhere('student_id', $studentId);
            $crossChecksForStudent = $crossCheckAttendances->where('student_id', $studentId);

            $status = $this->determineDailyStatus($rfidAttendance, $crossChecksForStudent);

            match ($status) {
                'present' => $counters['present']++,
                'sick' => $counters['sick']++,
                'permission' => $counters['permission']++,
                'alpha' => $counters['alpha']++,
                default => $counters['alpha']++,
            };
        }

        return $counters;
    }

    private function mapStudentAttendance($cs, string $date, $attendances, $crossCheckAttendances): ?array
    {
        if (!$cs->student || !$cs->student->user) {
            return null;
        }

        $studentId = $cs->student_id;

        $studentImage = $cs->student->image
            ? asset('storage/' . $cs->student->image)
            : null;

        $rfidAttendance = $attendances->firstWhere('student_id', $studentId);
        $crossChecksForStudent = $crossCheckAttendances->where('student_id', $studentId);

        $statusValue = $this->determineDailyStatus($rfidAttendance, $crossChecksForStudent);

        // Mapping statusValue to label
        $labels = [
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'sick' => 'Sakit',
            'permission' => 'Izin',
            'alpha' => 'Alpha',
        ];

        return [
            'student_image' => $studentImage,
            'student_name'  => $cs->student->user->name,
            'nisn'          => $cs->student->nisn,
            'status'        => [
                'value' => $statusValue,
                'label' => $labels[$statusValue] ?? 'Alpha',
            ],
            'date'          => $date,
        ];
    }

    private function determineDailyStatus($rfidAttendance, $crossCheckAttendancesForStudent): string
    {
        $lockedAttendance = $crossCheckAttendancesForStudent->firstWhere('is_locked', true);
        if ($lockedAttendance) {
            return $lockedAttendance->status->value;
        }

        if ($crossCheckAttendancesForStudent->isNotEmpty()) {
            $hasPresent = $crossCheckAttendancesForStudent->contains(fn($a) => $a->status->value === AttendanceStatusEnum::PRESENT->value);
            $hasSick = $crossCheckAttendancesForStudent->contains(fn($a) => $a->status->value === AttendanceStatusEnum::SICK->value);
            $hasPermission = $crossCheckAttendancesForStudent->contains(fn($a) => $a->status->value === AttendanceStatusEnum::PERMISSION->value);
            
            if ($hasPresent) {
                return 'present';
            } elseif ($hasSick) {
                return 'sick';
            } elseif ($hasPermission) {
                return 'permission';
            } else {
                return 'alpha';
            }
        }

        if ($rfidAttendance) {
            return $rfidAttendance->status->value;
        }

        return 'alpha';
    }

    public function generateAttendanceRecap(User $teacher, Request $request): array
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        $status = $request->input('status');
        $classroom = $this->requireClassroom($teacher);

        $studentsCollection = $this->classroomStudentsRepository->getAllByClassroomForAttendanceRecap($classroom['id'], $date, null, $status);
        $attendances = $this->attendanceRfidRepository->getByClassroomAndDate($classroom['id'], $date);
        $crossCheckAttendances = $this->attendanceRepository->getByClassroomAndDate($classroom['id'], $date);

        $students = $studentsCollection->map(
            fn($cs) => $this->mapStudentAttendance($cs, $date, $attendances, $crossCheckAttendances)
        )->filter()->values();

        $counters = $this->countAttendance(
            $classroom['id'],
            $date,
            $attendances,
            $crossCheckAttendances
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
            'students' => $students,
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
