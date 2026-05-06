<?php

namespace App\Http\Resources\Operator;

use App\Enums\AttendanceStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class RfidTapHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_name' => $this->name,
            'classroom' => $this->classroom,
            'date' => $this->date->format('Y-m-d'),
            'status' => $this->status,
            'status_label' => $this->status instanceof AttendanceStatusEnum 
                ? $this->status->label() 
                : ($this->status ? AttendanceStatusEnum::tryFrom($this->status)?->label() : null),
            'checkin_time' => $this->checkin_time
                ? Carbon::parse($this->checkin_time)->format('H:i')
                : null,
        ];
    }
}
