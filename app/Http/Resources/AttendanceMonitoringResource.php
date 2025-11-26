<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceMonitoringResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'student_id'     => $this->student_id,
            'student_name'   => $this->student_name,
            'classroom_name' => $this->classroom_name,
            'major_code'     => $this->major_code,
            'kehadiran'      => (int) $this->kehadiran,
            'sakit'          => (int) $this->sakit,
            'izin'           => (int) $this->izin,
            'alpha'          => (int) $this->alpha,  
        ];
    }
}
