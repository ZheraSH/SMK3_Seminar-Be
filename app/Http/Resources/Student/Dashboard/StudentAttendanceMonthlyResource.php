<?php

namespace App\Http\Resources\Student\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentAttendanceMonthlyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'month' => (int) $this->resource['month'],
            'label' => $this->monthLabel($this->resource['month']),
            'hadir' => (int) ($this->resource['hadir'] ?? 0),
            'telat' => (int) ($this->resource['telat'] ?? 0),
            'alpha' => (int) ($this->resource['alpha'] ?? 0),
        ];
    }

    private function monthLabel(int $month): string
    {
        return [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ][$month] ?? '-';
    }
}
