<?php

namespace App\Http\Resources\Operator;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\TapHelper;

class AttendanceRuleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'day' => [
                'value'=> $this->day,
                'label' => $this->day?->label(),
            ],
            'checkin_start' => TapHelper::parseRuleTimeToCarbon($this->checkin_start)?->format('H:i:s'),
            'checkin_end' => TapHelper::parseRuleTimeToCarbon($this->checkin_end)?->format('H:i:s'),
            'checkout_start' => TapHelper::parseRuleTimeToCarbon($this->checkout_start)?->format('H:i:s'),
            'checkout_end' => TapHelper::parseRuleTimeToCarbon($this->checkout_end)?->format('H:i:s'),
            'is_holiday' => (bool) $this->is_holiday,
        ];
    }
}