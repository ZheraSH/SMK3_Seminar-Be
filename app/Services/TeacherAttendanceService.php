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
    private AttendanceInterface $attendance;
    private LessonScheduleInterface $lessonSchedule;
    private ClassroomStudentsInterface $classroomStudents;

    public function __construct(
        AttendanceInterface $attendance,
        LessonScheduleInterface $lessonSchedule,
        ClassroomStudentsInterface $classroomStudents
    ) {
        $this->attendance = $attendance;
        $this->lessonSchedule = $lessonSchedule;
        $this->classroomStudents = $classroomStudents;
    }

    public function getCrossCheckData(string $classroomId, string $date, int $lessonOrder): array
    {
        if ($lessonOrder < 2) {
            throw new \Exception('Cross-check hanya untuk jam pelajaran ke-2 dan seterusnya');
        }

        $teacherId = auth()->user()->employee->id;
        $day = strtolower(Carbon::parse($date)->englishDayOfWeek);
        
        $lessonSchedule = $this->lessonSchedule->getByTeacherClassroomAndLessonOrder(
            $teacherId,
            $classroomId,
            $day,
            $lessonOrder
        );
        
        if (!$lessonSchedule) {
            throw new \Exception('Anda tidak memiliki jadwal mengajar untuk kelas ini pada jam pelajaran ini');
        }

        $students = $this->classroomStudents->getByClassroom($classroomId);
        
        $attendanceData = [];
        foreach ($students as $classroomStudent) {
            $student = $classroomStudent->student;
            
            $existingAttendance = $this->attendance->getByStudentLesson(
                $student->id,
                $date,
                $lessonOrder
            );

            $attendanceData[] = [
                'student_id' => $student->id,
                'name' => $student->user->name,
                'nisn' => $student->nisn,
                'existing_attendance' => $existingAttendance ? [
                    'id' => $existingAttendance->id,
                    'status' => $existingAttendance->status->value,
                    'status_label' => $existingAttendance->status->label(),
                ] : null,
            ];
        }

        $summary = $this->getClassroomSummary($classroomId, $date);

        return [
            'lesson_schedule' => [
                'id' => $lessonSchedule->id,
                'subject' => $lessonSchedule->subject->name,
                'lesson_order' => $lessonSchedule->lesson_order,
                'start_time' => $lessonSchedule->lessonHour->start_time,
                'end_time' => $lessonSchedule->lessonHour->end_time,
            ],
            'summary' => $summary,
            'students' => $attendanceData,
            'classroom' => [
                'id' => $classroomId,
                'name' => $students->first()->classroom->name ?? 'Unknown',
            ],
            'date' => $date,
            'lesson_order' => $lessonOrder,
        ];
    }

    public function submitCrossCheck(array $data, string $teacherId): array
    {
        return DB::transaction(function () use ($data, $teacherId) {
            $day = strtolower(Carbon::parse($data['date'])->englishDayOfWeek);
            
            $lessonSchedule = $this->lessonSchedule->getByTeacherClassroomAndLessonOrder(
                $teacherId,
                $data['classroom_id'],
                $day,
                $data['lesson_order']
            );
            
            if (!$lessonSchedule) {
                throw new \Exception('Anda tidak memiliki jadwal mengajar untuk kelas ini pada jam pelajaran ini');
            }
            
            $results = [];

            foreach ($data['attendances'] as $attendanceData) {
                $existing = $this->attendance->getByStudentLesson(
                    $attendanceData['student_id'],
                    $data['date'],
                    $data['lesson_order']
                );

                $attendancePayload = [
                    'student_id' => $attendanceData['student_id'],
                    'classroom_student_id' => $this->getClassroomStudentId($attendanceData['student_id'], $data['classroom_id']),
                    'teacher_id' => $teacherId,
                    'subject_id' => $data['subject_id'],
                    'lesson_schedule_id' => $data['lesson_schedule_id'],
                    'date' => $data['date'],
                    'lesson_order' => $data['lesson_order'],
                    'attendance_type' => 'cross_check',
                    'status' => $attendanceData['status'],
                    'proof' => AttendanceProofEnum::CLASSROOM,
                ];

                if ($existing) {
                    $attendance = $this->attendance->update($existing->id, $attendancePayload);
                } else {
                    $attendance = $this->attendance->store($attendancePayload);
                }

                $results[] = $attendance;
            }

            return $results;
        });
    }

    public function getTeacherSchedule(string $teacherId, string $date)
    {
        $day = strtolower(Carbon::parse($date)->englishDayOfWeek);
        return $this->lessonSchedule->getByTeacherAndDay($teacherId, $day);
    }

    public function getClassroomSummary(string $classroomId, string $date): array
    {
        $attendances = $this->attendance->getByClassroomAndDate($classroomId, $date);
        
        $summary = [
            'total_students' => $this->classroomStudents->countActiveByClassroom($classroomId),
            'present' => 0,
            'late' => 0,
            'alpha' => 0,
            'leave' => 0,
            'sick' => 0,
        ];

        foreach ($attendances as $attendance) {
            if ($attendance->lesson_order === 1) {
                switch ($attendance->status) {
                    case AttendanceStatusEnum::PRESENT:
                        $summary['present']++;
                        break;
                    case AttendanceStatusEnum::LATE:
                        $summary['late']++;
                        break;
                    case AttendanceStatusEnum::ALPHA:
                        $summary['alpha']++;
                        break;
                    case AttendanceStatusEnum::LEAVE:
                        $summary['leave']++;
                        break;
                    case AttendanceStatusEnum::SICK:
                        $summary['sick']++;
                        break;
                }
            }
        }

        return $summary;
    }

    private function getClassroomStudentId(string $studentId, string $classroomId): ?string
    {
        $classroomStudent = $this->classroomStudents->getByStudentAndClassroom($studentId, $classroomId);
        return $classroomStudent->id ?? null;
    }
}