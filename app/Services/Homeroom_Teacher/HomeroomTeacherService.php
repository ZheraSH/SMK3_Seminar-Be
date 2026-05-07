<?php

namespace App\Services\Homeroom_Teacher;

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

    public function __construct(ClassroomRepository $classroomRepository, ClassroomStudentsRepository $classroomStudentsRepository, AttendanceRfidRepository $attendanceRfidRepository)
    {
        $this->classroomRepository = $classroomRepository;
        $this->classroomStudentsRepository = $classroomStudentsRepository;
        $this->attendanceRfidRepository = $attendanceRfidRepository;
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

        $counters = $this->countAttendance(
            $classroom['id'],
            $date,
            $attendances
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
            $attendances = $this->attendanceRfidRepository->getByClassroomAndDate($classroom['id'], $date);

            $count = $this->countAttendance(
                $classroom['id'],
                $date,
                $attendances
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

        $students = $studentsPaginated->map(
            fn($cs) => $this->mapStudentAttendance($cs, $date, $attendances)
        );

        return [
            'students' => $students->values(),
            'meta' => $this->classroomStudentsRepository->formatPagination($studentsPaginated),
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

    private function countAttendance(string $classroomId, string $date, $attendances): array
    {
        $counters = [
            'present'    => 0,
            'late'       => 0,   // RFID: tap masuk terlambat
            'sick'       => 0,   // dari izin / cross-check manual
            'permission' => 0,   // dari izin / cross-check manual
            'alpha'      => 0,
        ];

        $students = $this->classroomStudentsRepository->getActiveStudentIds($classroomId);

        foreach ($students as $studentId) {
            $attendance = $attendances->firstWhere('student_id', $studentId);

            if (!$attendance) {
                $counters['alpha']++;
                continue;
            }

            match ($attendance->status) {
                RfidAttendanceStatusEnum::PRESENT => $counters['present']++,
                RfidAttendanceStatusEnum::LATE => $counters['late']++,
                RfidAttendanceStatusEnum::ALPHA => $counters['alpha']++,
            };
        }

        return $counters;
    }

    private function mapStudentAttendance($cs, string $date, $attendances): ?array
    {
        if (!$cs->student || !$cs->student->user) {
            return null;
        }

        $studentId = $cs->student_id;

        $studentImage = $cs->student->image
            ? asset('storage/' . $cs->student->image)
            : null;

        $attendance = $attendances->firstWhere('student_id', $studentId);

        $statusValue = $attendance?->status?->value ?? RfidAttendanceStatusEnum::ALPHA->value;

        return [
            'student_image' => $studentImage,
            'student_name'  => $cs->student->user->name,
            'nisn'          => $cs->student->nisn,
            'status'        => [
                'value' => $statusValue,
                'label' => RfidAttendanceStatusEnum::tryFrom($statusValue)?->label() ?? 'Alpha',
            ],
            'date'          => $date,
        ];
    }

    public function generateAttendanceRecap(User $teacher, Request $request): array
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        $status = $request->input('status');
        $classroom = $this->requireClassroom($teacher);

        $studentsCollection = $this->classroomStudentsRepository->getAllByClassroomForAttendanceRecap($classroom['id'], $date, null, $status);
        $attendances = $this->attendanceRfidRepository->getByClassroomAndDate($classroom['id'], $date);

        $students = $studentsCollection->map(
            fn($cs) => $this->mapStudentAttendance($cs, $date, $attendances)
        )->filter()->values();

        $counters = $this->countAttendance(
            $classroom['id'],
            $date,
            $attendances
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
