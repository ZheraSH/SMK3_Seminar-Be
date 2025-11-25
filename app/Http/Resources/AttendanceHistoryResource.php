<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'hari' => $this->date->translatedFormat('l'),
            'tanggal' => $this->date->format('d/m/y'),
            'status' => $this->status->label(),

            'jam_masuk' => $this->checkin_time 
                ? date('H:i', strtotime($this->checkin_time)) 
                : null,

            'jam_pulang' => $this->checkout_time 
                ? date('H:i', strtotime($this->checkout_time)) 
                : null,
        ];
    }
}
