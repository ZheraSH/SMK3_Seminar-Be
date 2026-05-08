<?php

namespace App\Services\Counselor;

use App\Contracts\Repositories\AttendanceRepository;
use App\Contracts\Repositories\AttendanceRfidRepository;
use App\Contracts\Repositories\Operator\LessonScheduleRepository;
use App\Contracts\Repositories\Operator\ClassroomStudentsRepository;
use App\Enums\AttendanceStatusEnum;
use App\Enums\PermissionTypeEnum;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class CounselorService
{
    private AttendanceRepository $attendanceRepository;
    private AttendanceRfidRepository $attendanceRfidRepository;
    private LessonScheduleRepository $lessonScheduleRepository;
    private ClassroomStudentsRepository $classroomStudentsRepository;

    public function __construct(AttendanceRepository $attendanceRepository, AttendanceRfidRepository $attendanceRfidRepository, LessonScheduleRepository $lessonScheduleRepository, ClassroomStudentsRepository $classroomStudentsRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
        $this->attendanceRfidRepository = $attendanceRfidRepository;
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
        $data = $this->attendanceRfidRepository->countTotalStatusGlobal();

        $total = $data['total'] ?: 1;

        return [
            'counts' => [
                'hadir' => (int) $data['hadir'],
                'terlambat' => (int) $data['terlambat'],
                'alpha' => (int) $data['alpha'],
                'total' => (int) $data['total'],
            ],
            'percentages' => [
                'hadir' => round(($data['hadir'] / $total) * 100, 2),
                'terlambat' => round(($data['terlambat'] / $total) * 100, 2),
                'alpha' => round(($data['alpha'] / $total) * 100, 2),
            ]
        ];
    }

    public function getMonthlyAttendanceStats(Request $request): array
    {
        $year = $request->input('year', Carbon::now()->year);
        return $this->attendanceRfidRepository->countTotalStatusMonthly((int) $year);
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

        $monthlySummaries = $this->attendanceRfidRepository
            ->getMonthlyAttendanceSummaryPerStudent($month, $year)
            ->keyBy('student_id');

        $students = $studentsPaginated->map(function ($cs) use ($monthlySummaries) {
            $studentId = $cs->student_id;
            $summary = $monthlySummaries->get($studentId);

            return [
                'student_name' => $cs->student->user->name,
                'classroom'    => $cs->classroom->name,
                'hadir'        => (int) (($summary?->hadir ?? 0) + ($summary?->telat ?? 0)),
                'izin'         => 0,
                'sakit'        => 0,
                'alpha'        => (int) ($summary?->alpha ?? 0),
            ];
        });

        return [
            'students' => $students,
            'meta' => $this->classroomStudentsRepository->formatPagination($studentsPaginated),
        ];
    }
}
