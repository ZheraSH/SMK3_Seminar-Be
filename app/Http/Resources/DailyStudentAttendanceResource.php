<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DailyStudentAttendanceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'student_uuid' => $this['student_uuid'],
            'student_name' => $this['student_name'],
            'nisn' => $this['nisn'],
            'status' => $this['status'],
            'date' => $this['date'],
        ];
    }
}