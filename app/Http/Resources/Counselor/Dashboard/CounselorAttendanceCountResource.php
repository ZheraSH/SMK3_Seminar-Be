<?php

namespace App\Http\Resources\Counselor\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CounselorAttendanceCountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'hadir' => $this['hadir'] ?? 0,
            'sakit' => $this['sakit'] ?? 0,
            'izin' => $this['izin'] ?? 0,
            'alpa' => $this['alpha'] ?? 0,
            'total_students_recorded' => array_sum($this->resource),
        ];
    }
}
