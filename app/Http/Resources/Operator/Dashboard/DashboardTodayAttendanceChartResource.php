<?php

namespace App\Http\Resources\Operator\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardTodayAttendanceChartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $total = (int) $this['total_students'];

        return [
            'total_students' => $total,

            'present' => [
                'count' => $this['present'] ?? 0,
                'percentage' => $total ? round((($this['present'] ?? 0) / $total) * 100, 2) : 0,
            ],
            'late' => [
                'count' => $this['late'] ?? 0,
                'percentage' => $total ? round((($this['late'] ?? 0) / $total) * 100, 2) : 0,
            ],
            'alpha' => [
                'count' => $this['absent'] ?? 0,
                'percentage' => $total ? round((($this['absent'] ?? 0) / $total) * 100, 2) : 0,
            ],
        ];
    }
}
