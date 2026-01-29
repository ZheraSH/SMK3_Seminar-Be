<?php

namespace App\Http\Resources\Operator;

use Illuminate\Http\Resources\Json\JsonResource;

class TapResultResource extends JsonResource
{
    public function toArray($request)
    {
        $data = is_array($this->resource) ? $this->resource : $this->resource->toArray();
        return [
            'status' => $data['status'] ?? null,
            'message' => $data['message'] ?? null,
            'type' => $data['type'] ?? null,
            'attendance_status' => $data['attendance_status'] ?? null,
            'requires_manual_attendance' => $data['requires_manual_attendance'] ?? false,
            'student' => $data['student'] ?? null,
            'rfid' => $data['rfid'] ?? null,
            'attendance' => $data['attendance'] ?? null,
            'timestamp' => $data['timestamp'] ?? now()->toISOString(),
            'indonesian_time' => $data['indonesian_time'] ?? null,
        ];
    }
}
