<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonHourResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'start' => $this->formatTime($this->start),
            'end'   => $this->formatTime($this->end),
            'time_range' => $this->formatTime($this->start) . ' - ' . $this->formatTime($this->end),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function formatTime($time): string
    {
        if (!$time) {
            return '';
        }

        if (is_string($time)) {
    
            if (preg_match('/^\d{1,2}\.\d{2}$/', $time)) {
                return $time;
            }
            

            if (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $time)) {
                $time = substr($time, 0, 5); // Ambil hanya HH:MM
                return str_replace(':', '.', $time);
            }
            
            if (preg_match('/^\d{1,2}:\d{2}$/', $time)) {
                return str_replace(':', '.', $time);
            }
            
            return $time;
        }

        if ($time instanceof \DateTime || $time instanceof \Carbon\Carbon) {
            return $time->format('H.i');
        }

        return (string) $time;
    }
}