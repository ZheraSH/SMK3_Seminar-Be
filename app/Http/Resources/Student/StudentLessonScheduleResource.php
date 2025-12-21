<?php

namespace App\Http\Resources\Student;

use App\Enums\DayEnum;
use App\Traits\Resources\FormatsTimeTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentLessonScheduleResource extends JsonResource
{
    use FormatsTimeTrait;

    public function toArray($request)
    {
        return [
            'classroom' => [
                'id' => $this['classroom']->id,
                'name' => $this['classroom']->name,
            ],
            'day' => [
                'value' => $this['day'],
                'label' => DayEnum::tryFrom($this['day'])?->label(),
            ],
            'schedules' => $this['schedules']->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'lesson_hour' => [
                        'name' => $schedule->lessonHour->name,
                        'time' => $this->formatTimeRange(
                            $schedule->lessonHour->start,
                            $schedule->lessonHour->end
                        ),
                    ],
                    'subject' => $schedule->subject?->name,
                    'teacher' => $schedule->teacher?->user?->name,
                ];
            })->values(),
        ];
    }
}