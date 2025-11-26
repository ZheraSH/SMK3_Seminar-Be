<?php

namespace App\Services;

use App\Contracts\Interfaces\AttendanceInterface;
use App\Contracts\Interfaces\LessonScheduleInterface;
use App\Contracts\Interfaces\ClassroomStudentsInterface;
use App\Enums\AttendanceStatusEnum;
use App\Enums\AttendanceProofEnum;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TeacherAttendanceService
{
    private AttendanceInterface $attendanceInterface;
    private LessonScheduleInterface $lessonScheduleInterface;
    private ClassroomStudentsInterface $classroomStudentsInterface;
    public function __construct(AttendanceInterface $attendanceInterface, LessonScheduleInterface $lessonScheduleInterface, ClassroomStudentsInterface $classroomStudentsInterface)
    {
        $this->attendanceInterface = $attendanceInterface;
        $this->lessonScheduleInterface = $lessonScheduleInterface;
        $this->classroomStudentsInterface = $classroomStudentsInterface;
    }

    public function getCrossCheckData(string $teacherId, string $classroomId, string $date, int $lessonOrder)
    {
        if ($lessonOrder < 2) {
            throw new \Exception('Cross-check hanya untuk jam pelajaran ke-2 dan seterusnya');
        }

        $day = strtolower(Carbon::parse($date)->englishDayOfWeek);
        $schedule = $this->lessonScheduleInterface->getByTeacherClassroomAndLessonOrder(
            $teacherId, $classroomId, $day, $lessonOrder
        );

        $students = $this->classroomStudentsInterface->getByClassroom($classroomId);
        $attendanceData = $students->map(function ($classroomStudent) use ($date, $lessonOrder) {
            $student = $classroomStudent->student;
            $existingAttendance = $this->attendanceInterface->getByStudentLesson($student->id, $date, $lessonOrder);

            return [
                'student_id' => $student->id,
                'name' => $student->user->name,
                'nisn' => $student->nisn,
                'existing_attendance' => $existingAttendance ? [
                    'id' => $existingAttendance->id,
                    'status' => $existingAttendance->status,
                    'status_label' => $existingAttendance->status->label(),
                ] : null,
            ];
        })->toArray();

        $summary = $this->getClassroomSummary($classroomId, $date);

        return (object)[
            'lesson_schedule' => $schedule,
            'summary' => $summary,
            'students' => $attendanceData,
            'classroom' => $students->first()?->classroom ?? null,
            'date' => $date,
            'lesson_order' => $lessonOrder,
            'total_students' => $summary['total_students'],
            'present' => $summary['present'],
            'late' => $summary['late'],
            'alpha' => $summary['alpha'],
            'leave' => $summary['leave'],
            'sick' => $summary['sick'],
        ];
    }

    public function submitCrossCheck(array $data, string $teacherId): array
    {
        return DB::transaction(function () use ($data, $teacherId) {
            $day = strtolower(Carbon::parse($data['date'])->englishDayOfWeek);

            $this->validateTeacherSchedule($teacherId, $data['classroom_id'], $day, $data['lesson_order']);

            $results = [];
            foreach ($data['attendances'] as $attendanceData) {
                $results[] = $this->processStudentAttendance($teacherId, $attendanceData, $data);
            }

            return $results;
        });
    }

    private function validateTeacherSchedule(string $teacherId, string $classroomId, string $day, int $lessonOrder): void
    {
        $schedule = $this->lessonScheduleInterface->getByTeacherClassroomAndLessonOrder(
            $teacherId, $classroomId, $day, $lessonOrder
        );
        if (!$schedule) {
            throw new \Exception('Anda tidak memiliki jadwal mengajar untuk kelas ini pada jam pelajaran ini');
        }
    }

    private function processStudentAttendance(string $teacherId, array $attendanceData, array $requestData)
    {
        $existing = $this->attendanceInterface->getByStudentLesson(
            $attendanceData['student_id'],
            $requestData['date'],
            $requestData['lesson_order']
        );

        $payload = $this->buildAttendancePayload($teacherId, $attendanceData, $requestData);

        if ($existing) {
            return $this->attendanceInterface->update($existing->id, $payload);
        }

        return $this->attendanceInterface->store($payload);
    }

    private function buildAttendancePayload(string $teacherId, array $attendanceData, array $requestData): array
    {
        $classroomStudentId = $this->classroomStudentsInterface
            ->getByStudentAndClassroom($attendanceData['student_id'], $requestData['classroom_id'])?->id;

        return [
            'student_id' => $attendanceData['student_id'],
            'classroom_student_id' => $classroomStudentId,
            'teacher_id' => $teacherId,
            'subject_id' => $requestData['subject_id'],
            'lesson_schedule_id' => $requestData['lesson_schedule_id'],
            'date' => $requestData['date'],
            'lesson_order' => $requestData['lesson_order'],
            'attendance_type' => 'cross_check',
            'status' => $attendanceData['status'],
            'proof' => AttendanceProofEnum::CLASSROOM->value,
            'notes' => $attendanceData['notes'] ?? null,
        ];
    }

    private function getClassroomSummary(string $classroomId, string $date): array
    {
        $attendances = $this->attendanceInterface->getByClassroomAndDate($classroomId, $date);
        $totalStudents = $this->classroomStudentsInterface->countActiveByClassroom($classroomId);

        $summary = [
            'total_students' => $totalStudents,
            'present' => 0,
            'late' => 0,
            'alpha' => 0,
            'leave' => 0,
            'sick' => 0,
        ];

        foreach ($attendances as $att) {
            if ($att->lesson_order === 1) {
                $key = match($att->status) {
                    AttendanceStatusEnum::PRESENT->value => 'present',
                    AttendanceStatusEnum::LATE->value => 'late',
                    AttendanceStatusEnum::ALPHA->value => 'alpha',
                    AttendanceStatusEnum::LEAVE->value => 'leave',
                    AttendanceStatusEnum::SICK->value => 'sick',
                    default => null
                };
                if ($key) $summary[$key]++;
            }
        }

        return $summary;
    }

    public function getClassroomSchedule(string $classroomId, string $date)
    {
        $day = strtolower(Carbon::parse($date)->englishDayOfWeek);
        $schedules = $this->lessonScheduleInterface->getByClassroomAndDay($classroomId, $day);

        foreach ($schedules as $schedule) {
            $schedule->can_cross_check = $schedule->lesson_order >= 2;
            $hasCrossCheck = $this->attendanceInterface->getByScheduleAndDate($schedule->id, $date);
            $schedule->has_cross_checked = $hasCrossCheck->isNotEmpty();
        }

        return $schedules;
    }

    public function getScheduleWithAttendanceStatus(string $teacherId, string $date)
    {
        $day = strtolower(Carbon::parse($date)->englishDayOfWeek);
        $schedules = $this->lessonScheduleInterface->getByTeacherAndDay($teacherId, $day);

        foreach ($schedules as $schedule) {
            $schedule->can_cross_check = $schedule->lesson_order >= 2;
            $hasCrossCheck = $this->attendanceInterface->getByScheduleAndDate($schedule->id, $date);
            $schedule->has_cross_checked = $hasCrossCheck->isNotEmpty();
            $schedule->student_count = $this->classroomStudentsInterface->getByClassroom($schedule->classroom_id)->count();
        }

        return $schedules;
    }
}