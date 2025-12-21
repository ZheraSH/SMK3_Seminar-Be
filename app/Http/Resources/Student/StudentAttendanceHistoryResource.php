<?php

namespace App\Http\Resources\Student;

use App\Enums\DayEnum;
use App\Traits\Resources\HasEnumLabelsTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class StudentAttendanceHistoryResource extends JsonResource
{
    use HasEnumLabelsTrait;

    public function toArray(Request $request): array
    {
        $date = Carbon::parse($this->date)->timezone('Asia/Jakarta');
        $dayValue = strtolower($date->format('l'));
    
        return [
            'day' => [
                'value' => $dayValue,
                'label' => DayEnum::tryFrom($dayValue)?->label(),
            ],
            'date' => $date->format('Y-m-d'),
            'status' => [
                'value' => $this->getEnumValue($this->status),
                'label' => $this->getEnumLabel($this->status),
            ],
            'check_in' => $this->checkin_time
                ? Carbon::parse($this->checkin_time)->format('H:i')
                : null,
            'check_out' => $this->checkout_time
                ? Carbon::parse($this->checkout_time)->format('H:i')
                : null,
        ];
    }
}
