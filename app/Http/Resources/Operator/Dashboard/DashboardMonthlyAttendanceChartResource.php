<?php

namespace App\Http\Resources\Operator\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class DashboardMonthlyAttendanceChartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'month' => Carbon::create()->month($this['month'])->format('F'),
            'attendance_percentage' => $this['percentage'],
        ];
    }
}
