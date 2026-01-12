<?php

namespace App\Http\Resources\Homeroom_teacher\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeroomRfidLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'student_name' => $this->student->user->name ?? 'Unknown',
            'nisn' => $this->student->nisn,
            'checkin_time' => $this->checkin_time,
            'checkout_time' => $this->checkout_time,
        ];
    }
}
