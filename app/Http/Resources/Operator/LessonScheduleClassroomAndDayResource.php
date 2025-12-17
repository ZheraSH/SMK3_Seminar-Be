<?php

namespace App\Http\Resources\Operator;

use App\Enums\DayEnum;
use App\Traits\Resources\FormatsTimeTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonScheduleClassroomAndDayResource extends JsonResource
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
                $hour = $schedule->lessonHour;
                return [
                    'id' => $schedule->id,
                    'lesson_hour' => [
                        'id' => $hour->id,
                        'name' => $hour->name,
                        'time' => $this->formatTimeRange(
                            $hour->start,
                            $hour->end
                        ),
                    ],
                    'subject' => $schedule->subject ? [
                        'id' => $schedule->subject->id,
                        'name' => $schedule->subject->name,
                    ] : null,
                    'teacher' => $schedule->teacher ? [
                        'id' => $schedule->teacher->id,
                        'name' => $schedule->teacher->user?->name,
                    ] : null,
                ];
            })->values(),

        ];
    }
}