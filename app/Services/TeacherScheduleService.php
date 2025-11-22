<?php

namespace App\Services;

use App\Contracts\Interfaces\LessonScheduleInterface;
use App\Contracts\Interfaces\AttendanceInterface;
use App\Contracts\Interfaces\ClassroomStudentsInterface;
use Carbon\Carbon;

class TeacherScheduleService
{
    public function __construct(
        private LessonScheduleInterface $lessonScheduleInterface,
        private AttendanceInterface $attendanceInterface,
        private ClassroomStudentsInterface $classroomStudentsInterface
    ) {}

    public function getDailySchedule(string $teacherId, string $date): array
    {
        $day = strtolower(Carbon::parse($date)->englishDayOfWeek);
        
        $schedules = $this->lessonScheduleInterface->getByTeacherAndDay($teacherId, $day);
        
        // Add cross-check information
        foreach ($schedules as $schedule) {
            $schedule->can_cross_check = $schedule->lesson_order >= 2;
            
            // Check if cross-check has been done for this schedule
            $hasCrossCheck = $this->attendanceInterface->getByScheduleAndDate(
                $schedule->id,
                $date
            );
            
            $schedule->has_cross_checked = $hasCrossCheck->isNotEmpty();
        }
        
        return $schedules;
    }

    public function getClassroomSchedule(string $classroomId, string $date): array
    {
        $day = strtolower(Carbon::parse($date)->englishDayOfWeek);
        
        $schedules = $this->lessonScheduleInterface->getByClassroomAndDay($classroomId, $day);
        
        // Add cross-check information
        foreach ($schedules as $schedule) {
            $schedule->can_cross_check = $schedule->lesson_order >= 2;
            
            // Check if cross-check has been done for this schedule
            $hasCrossCheck = $this->attendanceInterface->getByScheduleAndDate(
                $schedule->id,
                $date
            );
            
            $schedule->has_cross_checked = $hasCrossCheck->isNotEmpty();
        }
        
        return $schedules;
    }
}