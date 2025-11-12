<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'day' => $this->day?->value,
            'day_label' => $this->day?->label(),
            'checkin_start' => $this->checkin_start,
            'checkin_end' => $this->checkin_end,
            'checkout_start' => $this->checkout_start,
            'checkout_end' => $this->checkout_end,
            'is_holiday' => $this->is_holiday,
        ];
    }
}