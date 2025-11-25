<?php

namespace App\Http\Resources;

use App\Enums\DayEnum;
use App\Traits\FormatsTimeTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomDayScheduleResource extends JsonResource
{
    use FormatsTimeTrait;

    public function toArray($request)
    {
        $classroom = $this->resource['classroom'];
        $day = $this->resource['day'];
        $schedules = $this->resource['schedules'];

        return [
            'classroom' => [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'homeroom_teacher' => $classroom->employee?->user?->name,
                'total_students' => $classroom->classroomStudents
                    ?->where('status', \App\Enums\StudentStatusEnum::ACTIVE)
                    ->count() ?? 0,
            ],
            'day' => [
                'value' => $day,
                'label' => DayEnum::tryFrom($day)?->label(),
            ],
            'schedules' => $schedules->map(function ($schedule) {
                $hour = $schedule->lessonHour;

                return [
                    'id' => $schedule->id,
                    'lesson_hour_id' => $schedule->lesson_hour_id,
                    'placement' => $hour?->name,
                    'time' => $this->formatTimeRange($hour?->start, $hour?->end),
                    'subject' => $schedule->subject?->name,
                    'subject_teacher' => $schedule->employee?->user?->name,
                    'is_break' => !$hour?->is_lesson,
                ];
            })->values(),
        ];
    }
}