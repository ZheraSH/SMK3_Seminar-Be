<?php

namespace App\Http\Resources\Homeroom_teacher;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentAttendanceListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'student_image' => $this['student_image'],
            'student_name' => $this['student_name'],
            'nisn' => $this['nisn'],
            'status' => $this['status'],
            'date' => $this['date'],
        ];
    }
}
