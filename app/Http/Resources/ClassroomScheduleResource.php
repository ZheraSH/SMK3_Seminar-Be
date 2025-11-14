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

        return [
            'classroom' => [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'homeroom_teacher' => $classroom->employee?->user?->name,
                'total_students' => $classroom->classroomStudents?->where('status', \App\Enums\StudentStatusEnum::ACTIVE)->count() ?? 0,
                'school_year' => $classroom->schoolYear?->name,
                'major' => $classroom->major?->name,
                'level_class' => $classroom->levelClass?->name,
            ],
            'schedules' => $this->getStructuredSchedules($schedules),
            'summary' => [
                'total_days_with_schedule' => $schedules->count(),
                'total_weekly_lessons' => $classroom->lessonSchedules?->count() ?? 0,
                'total_subjects' => $classroom->lessonSchedules?->unique('subject_id')->count() ?? 0,
                'total_teachers' => $classroom->lessonSchedules?->unique('employee_id')->count() ?? 0,
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
            'placement' => $schedule->lessonHour?->name,
            'time' => $this->formatTimeRange($schedule->lessonHour?->start, $schedule->lessonHour?->end),
            'subject' => $schedule->subject?->name,
            'subject_teacher' => $schedule->employee?->user?->name,
        ];
    }
}