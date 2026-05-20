<?php

namespace App\Http\Resources\Homeroom_teacher\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeroomDailyStatsResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        $maxCount = max(
            $this['present'],
            $this['sick'],
            $this['permission'],
            $this['alpha']
        );

        return [
            'date' => $this['date'],
            'day_name' => $this['day_name'],
            'classroom' => $this['classroom_name'],
            'total_students' => $this['total_students'],
            'count' => [
                'present' => $this['present'],
                'sick' => $this['sick'],
                'permission' => $this['permission'],
                'alpa' => $this['alpha'],
            ],
            'attendance_percentage' => $this['total_students'] > 0
                ? round(($maxCount / $this['total_students']) * 100, 2)
                : 0,
        ];
    }
}
