<?php

namespace App\Http\Resources\Teacher;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\Resources\HasEnumLabelsTrait;
use App\Traits\Resources\FormatsTimeTrait;

class TeacherScheduleResource extends JsonResource
{
    use HasEnumLabelsTrait, FormatsTimeTrait;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'day' => [
                'value' => $this->getEnumValue($this->day),
                'label' => $this->getEnumLabel($this->day),
            ],
            'lesson_order' => $this->lessonHour?->lesson_order,
            'time' => $this->formatTimeRange(
                $this->lessonHour?->start,
                $this->lessonHour?->end
            ),
            'lesson_hour' => [
                'id' => $this->lessonHour?->id,
                'name' => $this->lessonHour?->name,
                'is_lesson' => $this->lessonHour?->is_lesson,
            ],
            'classroom' => $this->classroom ? [
                'id' => $this->classroom->id,
                'name' => $this->classroom->name,
            ] : null,
            'subject' => $this->subject ? [
                'id' => $this->subject->id,
                'name' => $this->subject->name,
            ] : null,
            'has_cross_checked' => (bool) ($this->has_cross_checked ?? false),
            'can_cross_check' => (bool) ($this->can_cross_check ?? false),
        ];
    }
}