<?php

namespace App\Http\Resources\Operator\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class DashboardActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_name' => $this->name,
            'classroom' => $this->classroom,
            'status' => $this->status,
            'status_label' => \App\Enums\AttendanceStatusEnum::from($this->status)->label(),
            'checkin_time' => $this->checkin_time
                ? Carbon::parse($this->checkin_time)->format('H:i')
                : null,
        ];
    }
}
