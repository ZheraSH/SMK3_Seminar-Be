<?php

namespace App\Http\Resources;

use App\Enums\DayEnum;
use App\Traits\FormatsTimeTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomDayScheduleResource extends JsonResource
{
    use FormatsTimeTrait;

    public function toArray(Request $request): array
    {
        $classroom = $this->resource['classroom'];
        $day = $this->resource['day'];
        $schedules = $this->resource['schedules'];

        return [
            'classroom' => [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'homeroom_teacher' => $classroom->employee?->user?->name,
                'total_students' => $classroom->classroomStudents?->where('status', \App\Enums\StudentStatusEnum::ACTIVE)->count() ?? 0,
            ],
            'day' => [
                'value' => $day,
                'label' => DayEnum::tryFrom($day)?->label(),
            ],
            'schedules' => $this->getDaySchedules($schedules),
        ];
    }

    private function getDaySchedules($schedules): array
    {
        return $schedules
            ->map(fn($schedule) => [
                'id' => $schedule->id,
                'placement' => $schedule->lessonHour?->name,
                'time' => $this->formatTimeRange($schedule->lessonHour?->start, $schedule->lessonHour?->end),
                'subject' => $schedule->subject?->name,
                'subject_teacher' => $schedule->employee?->user?->name,
            ])
            ->values()
            ->toArray();
    }
}