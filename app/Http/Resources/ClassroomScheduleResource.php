<?php

namespace App\Http\Resources;

use App\Enums\DayEnum;
use App\Traits\FormatsTimeTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomScheduleResource extends JsonResource
{
    use FormatsTimeTrait;
    
    public function toArray(Request $request): array
    {
        $classroom = $this->resource['classroom'];
        $schedules = $this->resource['schedules'];
        
        $totalStudents = 0;
        if (isset($classroom->classroomStudents)) {
            $totalStudents = $classroom->classroomStudents
                ->where('status', \App\Enums\ClassroomStudentStatusEnum::ACTIVE)
                ->count();
        }

        $totalWeeklyLessons = 0;
        $totalSubjects = 0;
        $totalTeachers = 0;
        $totalDaysWithSchedule = 0;
        
        if (isset($classroom->lessonSchedules)) {
            $totalWeeklyLessons = $classroom->lessonSchedules->count();
            $totalSubjects = $classroom->lessonSchedules->unique('subject_id')->count();
            $totalTeachers = $classroom->lessonSchedules->unique('employee_id')->count();
            $totalDaysWithSchedule = $classroom->lessonSchedules->groupBy('day')->count();
        }

        return [
            'classroom' => [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'homeroom_teacher' => $classroom->employee?->user?->name ?? '-',
                'total_students' => $totalStudents,
                'school_year' => $classroom->schoolYear?->name ?? '-',
                'major' => $classroom->major?->name ?? '-',
                'level_class' => $classroom->levelClass?->name ?? '-',
            ],
            'schedules' => $this->getStructuredSchedules($schedules),
            'summary' => [
                'total_days_with_schedule' => $totalDaysWithSchedule,
                'total_weekly_lessons' => $totalWeeklyLessons,
                'total_subjects' => $totalSubjects,
                'total_teachers' => $totalTeachers,
            ],
        ];
    }

    private function getStructuredSchedules($schedules): array
    {
        $structured = [];

        foreach (DayEnum::cases() as $day) {
            $daySchedules = $schedules->get($day->value, collect())
                ->sortBy('lesson_hour_id')
                ->map(fn($schedule) => $this->formatSchedule($schedule))
                ->values();

            $structured[$day->value] = [
                'day_label' => $day->label(),
                'total_lessons' => $daySchedules->count(),
                'schedules' => $daySchedules->toArray()
            ];
        }

        return $structured;
    }

    private function formatSchedule($schedule): array
    {
        return [
            'id' => $schedule->id,
            'placement' => $schedule->lessonHour?->name ?? '-',
            'time' => $this->formatTimeRange($schedule->lessonHour?->start, $schedule->lessonHour?->end),
            'subject' => $schedule->subject?->name ?? '-',
            'subject_teacher' => $schedule->employee?->user?->name ?? '-',
            'subject_id' => $schedule->subject_id,
            'employee_id' => $schedule->employee_id,
            'lesson_hour_id' => $schedule->lesson_hour_id,
        ];
    }
}