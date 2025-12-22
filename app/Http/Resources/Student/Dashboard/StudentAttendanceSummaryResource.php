<?php

namespace App\Http\Resources\Student\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentAttendanceSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'hadir' => (int) ($this->resource['hadir'] ?? 0),
            'telat' => (int) ($this->resource['telat'] ?? 0),
            'izin'  => (int) ($this->resource['izin'] ?? 0),
            'alpha' => (int) ($this->resource['alpha'] ?? 0),
        ];
    }
}
