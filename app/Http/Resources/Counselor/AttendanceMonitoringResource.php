<?php

namespace App\Http\Resources\Counselor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceMonitoringResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'student_name' => $this['student_name'],
            'classroom' => $this['classroom'],
            'hadir' => (int) $this['hadir'],
            'izin' => (int) $this['izin'],
            'sakit' => (int) $this['sakit'],
            'alpa' => (int) $this['alpha'],
        ];
    }
}
