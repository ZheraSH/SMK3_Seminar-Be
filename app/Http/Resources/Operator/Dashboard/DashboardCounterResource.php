<?php

namespace App\Http\Resources\Operator\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardCounterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_students' => $this['total_students'],
            'total_employees' => $this['total_employees'],
            'total_classrooms' => $this['total_classrooms'],
            'attendance_percentage_today' => $this['attendance_percentage_today'],
        ];
    }
}
