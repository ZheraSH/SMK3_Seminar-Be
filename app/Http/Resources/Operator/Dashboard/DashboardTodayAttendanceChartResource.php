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
            'sick' => [
                'count' => $this['sick'] ?? 0,
                'percentage' => $total ? round((($this['sick'] ?? 0) / $total) * 100, 2) : 0,
            ],
            'permission' => [
                'count' => $this['permission'] ?? 0,
                'percentage' => $total ? round((($this['permission'] ?? 0) / $total) * 100, 2) : 0,
            ],
            'alpa' => [
                'count' => $this['alpha'] ?? 0,
                'percentage' => $total ? round((($this['alpha'] ?? 0) / $total) * 100, 2) : 0,
            ],
        ];
    }
}
