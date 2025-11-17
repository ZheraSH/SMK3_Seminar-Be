<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TapResultResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'status' => $this['status'] ?? null,
            'message' => $this['message'] ?? null,
            'type' => $this['type'] ?? null,
            'attendance_status' => $this['attendance_status'] ?? null,
            'requires_manual_attendance' => $this['requires_manual_attendance'] ?? false,
            'student' => $this['student'] ?? null,
            'rfid' => $this['rfid'] ?? null,
            'attendance' => $this['attendance'] ?? null,
            'timestamp' => $this['timestamp'] ?? now()->toISOString(),
        ];
    }
}