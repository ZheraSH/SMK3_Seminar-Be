<?php

namespace App\Http\Resources\Operator;

use App\Traits\Resources\HasEnumLabelsTrait;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\TapHelper;

class AttendanceRuleAllResource extends JsonResource
{
    use HasEnumLabelsTrait;

    private const LATE_TOLERANCE_MINUTES = 10;

    public function toArray($request): array
    {
        $checkinEnd = TapHelper::parseRuleTimeToCarbon($this->checkin_end);
        $checkinEndWithTolerance = $checkinEnd?->copy()->addMinutes(self::LATE_TOLERANCE_MINUTES);

        return [
            'id' => $this->id,
            'day' => $this->getEnumValue($this->day),
            'checkin_start' => TapHelper::parseRuleTimeToCarbon($this->checkin_start)?->format('H:i:s'),
            'checkin_end' => $checkinEndWithTolerance?->format('H:i:s'),
            'checkout_start' => TapHelper::parseRuleTimeToCarbon($this->checkout_start)?->format('H:i:s'),
            'checkout_end' => TapHelper::parseRuleTimeToCarbon($this->checkout_end)?->format('H:i:s'),
            'is_holiday' => (bool) $this->is_holiday,
            'late_tolerance_minutes' => self::LATE_TOLERANCE_MINUTES,
        ];
    }
}
