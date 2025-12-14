<?php

namespace App\Http\Resources\Operator;

use App\Traits\Resources\FormatsTimeTrait;
use App\Traits\Resources\HasEnumLabelsTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonHourResource extends JsonResource
{
    use FormatsTimeTrait, HasEnumLabelsTrait;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'day' => [
                'value' => $this->getEnumValue($this->day),
                'label' => $this->getEnumLabel($this->day),
            ],
            'name' => $this->name,
            'start_time' => $this->formatTime($this->start),
            'end_time' => $this->formatTime($this->end),
            'time_range' => $this->formatTimeRange($this->start, $this->end),
            'is_lesson' => (bool) $this->is_lesson,
            'order' => $this->order,
            'type_label' => $this->is_lesson ? 'Jam Pelajaran' : 'Istirahat',
        ];
    }
}