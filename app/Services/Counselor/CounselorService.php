<?php

namespace App\Services\Counselor;

use App\Contracts\Repositories\AttendanceRepository;
use App\Contracts\Repositories\Operator\LessonScheduleRepository;
use App\Contracts\Repositories\Operator\ClassroomStudentsRepository;
use App\Enums\AttendanceStatusEnum;
use App\Enums\PermissionTypeEnum;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CounselorService
{
    private AttendanceRepository $attendanceRepository;
    private LessonScheduleRepository $lessonScheduleRepository;
    private ClassroomStudentsRepository $classroomStudentsRepository;

    public function __construct(AttendanceRepository $attendanceRepository, LessonScheduleRepository $lessonScheduleRepository, ClassroomStudentsRepository $classroomStudentsRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
        $this->lessonScheduleRepository = $lessonScheduleRepository;
        $this->classroomStudentsRepository = $classroomStudentsRepository;
    }

    public function verifyPermission(string $permissionId, string $status, string $studentId, string $startDate, string $endDate, string $permissionType): void
    {
        $attendanceStatus = match ($permissionType) {
            PermissionTypeEnum::SICK->value => AttendanceStatusEnum::SICK->value,
            PermissionTypeEnum::PERMISSION->value => AttendanceStatusEnum::PERMISSION->value,
            PermissionTypeEnum::DISPENSATION->value => AttendanceStatusEnum::PERMISSION->value,
            default => AttendanceStatusEnum::ALPHA->value,
        };

        $classroomStudent = $this->classroomStudentsRepository->getActiveStudentClassroom($studentId);
        if (!$classroomStudent) {
            return;
        }

        $classroomId = $classroomStudent->classroom_id;
        $classroomStudentId = $classroomStudent->id;

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($start->lte($end)) {
            $date = $start->format('Y-m-d');
            $dayEnglish = strtolower($start->locale('en')->dayName);
            $schedules = $this->lessonScheduleRepository->getLessonScheduleClassroomAndDay($classroomId, $dayEnglish);

            foreach ($schedules as $schedule) {
                $this->attendanceRepository->lockAttendance(
                    studentId: $studentId,
                    date: $date,
                    lessonOrder: $schedule->lessonHour->order,
                    status: $attendanceStatus,
                    permissionId: $permissionId,
                    lessonScheduleId: $schedule->id,
                    subjectId: $schedule->subject_id,
                    teacherId: $schedule->teacher_id,
                    classroomStudentId: $classroomStudentId
                );
            }

            $start->addDay();
        }
    }

    public function getGlobalAttendanceStats(): array
    {
        $data = $this->attendanceRepository->countTotalStatusGlobal();

        $total = $data['total'] ?: 1;

        return [
            'counts' => [
                'hadir' => (int) $data['hadir'],
                'sakit' => (int) $data['sakit'],
                'izin' => (int) $data['izin'],
                'alpa' => (int) $data['alpha'],
                'total' => (int) $data['total'],
            ],
            'percentages' => [
                'hadir' => round(($data['hadir'] / $total) * 100, 2),
                'sakit' => round(($data['sakit'] / $total) * 100, 2),
                'izin' => round(($data['izin'] / $total) * 100, 2),
                'alpa' => round(($data['alpha'] / $total) * 100, 2),
            ]
        ];
    }

    public function getMonthlyAttendanceStats(Request $request): array
    {
        $year = $request->input('year', Carbon::now()->year);
        $monthlyData = $this->attendanceRepository->countTotalStatusMonthly((int) $year);

        return collect($monthlyData)->map(function ($row) {
            $total = $row['hadir'] + $row['sakit'] + $row['izin'] + $row['alpha'];
            $attended = $row['hadir'] + $row['sakit'];

            return [
                'month' => $row['month'],
                'percentage' => $total > 0 ? round(($attended / $total) * 100, 2) : 0,
            ];
        })->toArray();
    }

    public function getGlobalDailyAttendance(Request $request): array
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        $search = $request->input('search');
        $status = $request->input('status');
        $perPage = $request->input('per_page', 10);

        $carbonDate = Carbon::parse($date);
        $month = $carbonDate->month;
        $year = $carbonDate->year;

        $studentsPaginated = $this->classroomStudentsRepository
            ->getGlobalDailyAttendance($date, $search, $status, $perPage);

        $monthlySummaries = $this->attendanceRepository
            ->getMonthlyAttendanceSummaryPerStudent($month, $year)
            ->keyBy('student_id');

        $students = $studentsPaginated->map(function ($cs) use ($monthlySummaries) {
            $studentId = $cs->student_id;
            $summary = $monthlySummaries->get($studentId);

            return [
                'student_name' => $cs->student->user->name,
                'classroom' => $cs->classroom->name,
                'hadir' => (int) ($summary?->hadir ?? 0),
                'izin' => (int) ($summary?->izin ?? 0),
                'sakit' => (int) ($summary?->sakit ?? 0),
                'alpha' => (int) ($summary?->alpha ?? 0),
            ];
        });

        return [
            'students' => $students,
            'pagination' => $this->classroomStudentsRepository->formatPagination($studentsPaginated),
        ];
    }
}
