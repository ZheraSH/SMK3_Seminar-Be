<?php

namespace App\Http\Resources\Homeroom_teacher\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeroomDailyStatsResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        $attendedCount = $this['present'] + $this['late'] + $this['permission'] + $this['sick'];

        return [
            'date' => $this['date'],
            'day_name' => $this['day_name'],
            'classroom' => $this['classroom_name'],
            'total_students' => $this['total_students'],
            'count' => [
                'present' => $this['present'],
                'late' => $this['late'],
                'permission' => $this['permission'] + $this['sick'],
                'alpha' => $this['alpha'],
            ],
            'attendance_percentage' => $this['total_students'] > 0
                ? round(($attendedCount / $this['total_students']) * 100, 2)
                : 0,
        ];
    }
}
