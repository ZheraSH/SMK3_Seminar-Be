<?php
namespace App\Http\Resources;

use App\Enums\DayEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonHourResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'day' => $this->day?->value,
            'day_label' => $this->day?->label() ?? $this->day,
            'name' => $this->name,
            'start_time' => $this->formatTime($this->start),
            'end_time' => $this->formatTime($this->end),
            'time_range' => $this->getTimeRange(),
        ];
    }

    private function formatTime($time): string
    {
        if (!$time) return '';
        if ($time instanceof \DateTime || $time instanceof \Carbon\Carbon) {
            return $time->format('H.i');
        }
        if (is_string($time) && preg_match('/^(\d{1,2}):(\d{2})/', $time, $matches)) {
            return $matches[1] . '.' . $matches[2];
        }
        return (string) $time;
    }

    private function getTimeRange(): string
    {
        $start = $this->formatTime($this->start);
        $end = $this->formatTime($this->end);
        return $start && $end ? $start . ' - ' . $end : '';
    }
}