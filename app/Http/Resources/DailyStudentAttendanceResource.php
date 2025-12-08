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
            'time_in' => $this['time_in'],
            'time_out' => $this['time_out'],
            'date' => $this['date'],
        ];
    }
}