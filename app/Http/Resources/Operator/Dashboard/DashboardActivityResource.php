<?php

namespace App\Http\Resources\Operator\Dashboard;

use App\Traits\Resources\HasEnumLabelsTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class DashboardActivityResource extends JsonResource
{
    use HasEnumLabelsTrait;
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_name' => $this->name,
            'classroom' => $this->classroom,
            'date' => $this->date instanceof Carbon
                ? $this->date->format('Y-m-d')
                : Carbon::parse($this->date)->format('Y-m-d'),
            'status' => [
                'value' => $this->getEnumValue($this->status),
                'label' => $this->getEnumLabel($this->status),
            ],
            'checkin_time' => $this->checkin_time
                ? Carbon::parse($this->checkin_time)->format('H:i')
                : null,
            'checkout_time' => $this->checkout_time
                ? Carbon::parse($this->checkout_time)->format('H:i')
                : null,
        ];
    }
}
