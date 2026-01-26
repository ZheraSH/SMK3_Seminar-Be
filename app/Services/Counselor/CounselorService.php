<?php

namespace App\Services\Counselor;

use App\Contracts\Repositories\AttendanceRepository;
use App\Contracts\Repositories\Operator\LessonScheduleRepository;
use App\Contracts\Repositories\Operator\ClassroomStudentsRepository;
use App\Enums\AttendanceStatusEnum;
use App\Enums\PermissionTypeEnum;
use Carbon\Carbon;

class CounselorService
{
    private AttendanceRepository $attendanceRepository;
    private LessonScheduleRepository $lessonScheduleRepository;
    private ClassroomStudentsRepository $classroomStudentsRepository;

    public function __construct(AttendanceRepository $attendanceRepository, LessonScheduleRepository $lessonScheduleRepository, ClassroomStudentsRepository $classroomStudentsRepository) {
        $this->attendanceRepository = $attendanceRepository;
        $this->lessonScheduleRepository = $lessonScheduleRepository;
        $this->classroomStudentsRepository = $classroomStudentsRepository;
    }

    public function verifyPermission(string $permissionId, string $status, string $studentId, string $startDate, string $endDate, string $permissionType): void
    {

        $attendanceStatus = match ($permissionType) {
            PermissionTypeEnum::SICK->value => AttendanceStatusEnum::SICK->value,
            PermissionTypeEnum::PERMISSION->value => AttendanceStatusEnum::LEAVE->value,
            PermissionTypeEnum::DISPENSATION->value => AttendanceStatusEnum::LEAVE->value,
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
            $dayName = strtolower($start->locale('id')->dayName);
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
                'terlambat' => (int) $data['terlambat'],
                'izin' => (int) $data['izin'],
                'alpha' => (int) $data['alpha'],
                'total' => (int) $data['total'],
            ],
            'percentages' => [
                'hadir' => round(($data['hadir'] / $total) * 100, 2),
                'terlambat' => round(($data['terlambat'] / $total) * 100, 2),
                'izin' => round(($data['izin'] / $total) * 100, 2),
                'alpha' => round(($data['alpha'] / $total) * 100, 2),
            ]
        ];
    }

    public function getMonthlyAttendanceStats(int $year): array
    {
        return $this->attendanceRepository->countTotalStatusMonthly($year);
    }
}
