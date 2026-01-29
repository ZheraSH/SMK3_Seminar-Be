<?php

namespace App\Http\Resources\Counselor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceMonthlyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'month' => $this->resource['month'],
            'hadir' => (int) $this->resource['hadir'],
            'terlambat' => (int) $this->resource['terlambat'],
            'izin' => (int) $this->resource['izin'],
            'alpha' => (int) $this->resource['alpha'],
        ];
    }
}
