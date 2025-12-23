<?php

namespace App\Http\Resources;

use App\Traits\FormatsTimeTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonScheduleResource extends JsonResource
{
    use FormatsTimeTrait;
    
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'day' => $this->day?->value,
            'day_label' => $this->day?->label(),
            'classroom' => $this->whenLoaded('classroom', fn() => $this->classroom->only(['id', 'name'])),
            'lesson_hour' => $this->whenLoaded('lessonHour', fn() => [
                'id' => $this->lessonHour->id,
                'name' => $this->lessonHour->name,
                'start' => $this->lessonHour->start,
                'end' => $this->lessonHour->end,
                'time' => $this->formatTimeRange($this->lessonHour->start, $this->lessonHour->end),
            ]),
            'subject' => $this->whenLoaded('subject', fn() => $this->subject->only(['id', 'name'])),
            'teacher' => $this->whenLoaded('teacher', function() {
                return $this->teacher?->user ? [
                    'id' => $this->teacher->id,
                    'name' => $this->teacher->user->name,
                ] : null;
            }),
        ];
    }
}