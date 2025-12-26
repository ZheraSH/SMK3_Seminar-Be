<?php

namespace App\Http\Resources\Homeroom_teacher;

use Illuminate\Http\Resources\Json\JsonResource;

class SummaryClassResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'date' => $this['date'],
            'day_name' => $this['day_name'],
            'classroom' => [
                'id' => $this['classroom_id'],
                'name' => $this['classroom_name'],
            ],
            'total_students' => $this['total_students'],
            'attendance' => [
                'present' => $this['present'],
                'late' => $this['late'],
                'sick' => $this['sick'],
                'permission' => $this['permission'],
                'alpha' => $this['alpha'],
            ],
            'attended_count' => 
                $this['present'] + 
                $this['late'] + 
                $this['sick'] + 
                $this['permission'],
            'attendance_percentage' => $this['percentage'],
        ];
    }
}