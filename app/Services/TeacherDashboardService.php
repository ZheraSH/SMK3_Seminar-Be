<?php

namespace App\Services;

use App\Contracts\Interfaces\LessonScheduleInterface;
use App\Contracts\Interfaces\AttendanceInterface;
use App\Contracts\Interfaces\ClassroomStudentsInterface;
use Carbon\Carbon;

class TeacherDashboardService
{
    public function __construct(
        private LessonScheduleInterface $lessonSchedule,
        private AttendanceInterface $attendance,
        private ClassroomStudentsInterface $classroomStudents
    ) {}

    /**
     * Get classroom list with attendance summary
     */
    public function getClassroomList(string $teacherId, string $date): array
    {
        $day = $this->getDayFromDate($date);
        $schedules = $this->lessonSchedule->getByTeacherAndDay($teacherId, $day);

        if ($schedules->isEmpty()) {
            return [];
        }

        $classroomData = [];
        foreach ($schedules as $schedule) {
            $classroomId = $schedule->classroom_id;
            
            if (!isset($classroomData[$classroomId])) {
                $classroomData[$classroomId] = [
                    'classroom' => $schedule->classroom,
                    'schedules' => [],
                    'attendance_summary' => $this->getClassroomAttendanceSummary($classroomId, $date),
                    'student_count' => $this->classroomStudents->getByClassroom($classroomId)->count()
                ];
            }
            
            $classroomData[$classroomId]['schedules'][] = [
                'subject' => $schedule->subject->name,
                'lesson_order' => $schedule->lesson_order,
                'start_time' => $schedule->lessonHour->start_time,
                'end_time' => $schedule->lessonHour->end_time,
                'attendance_status' => $this->getScheduleAttendanceStatus($schedule, $date)
            ];
        }

        return array_values($classroomData);
    }

    /**
     * Get today's schedules with attendance status (with-attendance yang dipindahkan)
     */
    public function getTodaySchedule(string $teacherId, string $date)
    {
        $day = $this->getDayFromDate($date);
        $schedules = $this->lessonSchedule->getByTeacherAndDay($teacherId, $day);

        foreach ($schedules as $schedule) {
            $schedule->can_cross_check = $schedule->lesson_order >= 2;
            $hasCrossCheck = $this->attendance->getByScheduleAndDate($schedule->id, $date);
            $schedule->has_cross_checked = $hasCrossCheck->isNotEmpty();
            $schedule->student_count = $this->classroomStudents->getByClassroom($schedule->classroom_id)->count();
            $schedule->attendance_status = $this->getScheduleAttendanceStatus($schedule, $date);
        }

        return $schedules->sortBy('lesson_order')->values();
    }

    /**
     * Get classroom attendance summary
     */
    private function getClassroomAttendanceSummary(string $classroomId, string $date): array
    {
        $day = $this->getDayFromDate($date);
        $schedules = $this->lessonSchedule->getByClassroomAndDay($classroomId, $day);

        $rfidCompleted = false;
        $crossCheckCompleted = 0;
        $totalCrossCheckAvailable = 0;

        foreach ($schedules as $schedule) {
            if ($schedule->lesson_order === 1) {
                $rfidCompleted = $this->isRfidCompleted($classroomId, $date);
            } else {
                $totalCrossCheckAvailable++;
                if ($this->isCrossCheckCompleted($schedule->id, $date)) {
                    $crossCheckCompleted++;
                }
            }
        }

        return [
            'rfid_completed' => $rfidCompleted,
            'cross_check_completed' => $crossCheckCompleted,
            'total_cross_check_available' => $totalCrossCheckAvailable,
            'is_fully_completed' => $rfidCompleted && ($crossCheckCompleted === $totalCrossCheckAvailable),
        ];
    }

    /**
     * Get schedule attendance status
     */
    private function getScheduleAttendanceStatus($schedule, string $date): string
    {
        if ($schedule->lesson_order === 1) {
            return $this->isRfidCompleted($schedule->classroom_id, $date) ? 'completed' : 'pending';
        } else {
            if ($this->isCrossCheckCompleted($schedule->id, $date)) {
                return 'completed';
            }
            return $this->isRfidCompleted($schedule->classroom_id, $date) ? 'cross-check-available' : 'pending';
        }
    }

    private function isRfidCompleted(string $classroomId, string $date): bool
    {
        $attendances = $this->attendance->getByClassroomAndDate($classroomId, $date);
        return $attendances->where('lesson_order', 1)
                          ->where('attendance_type', 'rfid')
                          ->isNotEmpty();
    }

    private function isCrossCheckCompleted(string $scheduleId, string $date): bool
    {
        $attendances = $this->attendance->getByScheduleAndDate($scheduleId, $date);
        return $attendances->where('attendance_type', 'cross_check')->isNotEmpty();
    }

    private function getDayFromDate(string $date): string
    {
        return strtolower(Carbon::parse($date)->englishDayOfWeek);
    }

    public function validateDate($date): string
    {
        if (!Carbon::createFromFormat('Y-m-d', $date)) {
            throw new \Exception('Format tanggal tidak valid', 400);
        }
        return $date;
    }
}