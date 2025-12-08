<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WeeklyAttendanceStatisticsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'period' => $this['period'],
            'classroom' => $this['classroom'],
            'totals' => $this['totals'],
            'daily_data' => $this['daily_data'],
        ];
    }
}