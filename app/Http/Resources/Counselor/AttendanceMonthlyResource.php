<?php

namespace App\Http\Resources\Counselor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class AttendanceMonthlyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'month' => Carbon::create()->month($this->resource['month'])->translatedFormat('F'),
            'attendance_percentage' => $this->resource['percentage'],
        ];
    }
}
