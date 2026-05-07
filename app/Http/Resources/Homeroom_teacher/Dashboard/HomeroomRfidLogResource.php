<?php

namespace App\Http\Resources\Homeroom_teacher\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeroomRfidLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'student_name'  => $this->student->user->name,
            'nisn'          => $this->student->nisn,
            'status'        => [
                'value' => $this->status?->value ?? null,
                'label' => $this->status?->label() ?? null,
            ],
            'checkin_time'  => $this->checkin_time,
            'checkout_time' => $this->checkout_time,
        ];
    }
}
