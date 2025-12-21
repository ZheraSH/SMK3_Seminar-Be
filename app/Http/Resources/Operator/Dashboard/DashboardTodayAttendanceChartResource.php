<?php

namespace App\Http\Resources\Operator\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardTodayAttendanceChartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $total = $this['total_students'];

        return [
            'total_students' => $total,

            'present' => [
                'count' => $this['present'],
                'percentage' => $total ? round(($this['present'] / $total) * 100, 2) : 0,
            ],
            'late' => [
                'count' => $this['late'],
                'percentage' => $total ? round(($this['late'] / $total) * 100, 2) : 0,
            ],
            'permission' => [
                'count' => $this['permission'],
                'percentage' => $total ? round(($this['permission'] / $total) * 100, 2) : 0,
            ],
            'absent' => [
                'count' => $this['absent'],
                'percentage' => $total ? round(($this['absent'] / $total) * 100, 2) : 0,
            ],
        ];
    }
}
