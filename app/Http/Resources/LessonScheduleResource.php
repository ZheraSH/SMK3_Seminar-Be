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
            'classroom' => $this->classroom?->name,
            'school_year' => $this->classroom?->schoolYear?->name,
            'day' => $this->day?->value,
            'day_label' => $this->day?->label() ?? $this->day,
            'placement' => $this->lessonHour?->name,
            'time' => $this->formatTimeRange($this->lessonHour?->start, $this->lessonHour?->end),
            'subject' => $this->subject?->name,
            'teacher' => $this->employee?->user?->name,
        ];
    }
}