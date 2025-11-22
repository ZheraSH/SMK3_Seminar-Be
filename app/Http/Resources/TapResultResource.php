<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TapResultResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'status' => $this['status'],
            'message' => $this['message'],
            'type' => $this['type'],
            'attendance_status' => $this['attendance_status'],
            'requires_manual_attendance' => $this['requires_manual_attendance'] ?? false,
            'student' => $this['student'],
            'rfid' => $this['rfid'],
            'attendance' => $this['attendance'],
            'timestamp' => $this['timestamp'],
        ];
    }
}