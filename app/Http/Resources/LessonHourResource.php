<?php

namespace App\Http\Resources;

use App\Traits\FormatsTimeTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonHourResource extends JsonResource
{
    use FormatsTimeTrait;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'day' => $this->day?->value,
            'day_label' => $this->day?->label() ?? $this->day,
            'name' => $this->name,
            'start_time' => $this->formatTime($this->start),
            'end_time' => $this->formatTime($this->end),
            'time_range' => $this->formatTimeRange($this->start, $this->end),
        ];
    }
}