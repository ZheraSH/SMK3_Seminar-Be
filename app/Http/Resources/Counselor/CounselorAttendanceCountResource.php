<?php

namespace App\Http\Resources\Counselor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CounselorAttendanceCountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'hadir' => $this['hadir'] ?? 0,
            'telat' => $this['telat'] ?? 0,
            'izin' => ($this['izin'] ?? 0) + ($this['sakit'] ?? 0),
            'alpha' => $this['alpha'] ?? 0,
            'total_students_recorded' => array_sum($this->resource),
        ];
    }
}
