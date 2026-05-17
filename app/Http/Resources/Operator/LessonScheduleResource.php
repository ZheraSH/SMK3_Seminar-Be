<?php

namespace App\Http\Resources\Operator;

use App\Traits\Resources\FormatsTimeTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonScheduleResource extends JsonResource
{
    use FormatsTimeTrait;
    
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'day' => [
                'value' => $this->day?->value,
                'label' => $this->day?->label(),
            ],
            'classroom' => $this->whenLoaded('classroom', fn() => $this->classroom->only(['id', 'name'])),
            'lesson_hour' => $this->whenLoaded('lessonHour', fn() => [
                'id' => $this->lessonHour->id,
                'name' => $this->lessonHour->name,
                'time' => $this->formatTimeRange($this->lessonHour->start, $this->lessonHour->end),
            ]),
            'subject' => $this->whenLoaded('subject', fn() => $this->subject ? $this->subject->only(['id', 'name']) : null),
            'teacher' => $this->whenLoaded('teacher', function() {
                return $this->teacher?->user ? [
                    'id' => $this->teacher->id,
                    'name' => $this->teacher->user->name,
                ] : null;
            }),
        ];
    }
}