<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'date' => $this->date?->format('d-m-Y'),
            'checkin_time' => $this->checkin_time?->format('H:i'),
            'checkout_time' => $this->checkout_time?->format('H:i'),
            'status' => $this->status?->value ?? $this->status,
            'status_label' => $this->status?->label() ?? null,
            'tap_type' => $this->tap_type?->value ?? $this->tap_type,
            'tap_type_label' => $this->tap_type?->label() ?? null,
            'proof' => $this->proof?->value ?? $this->proof,
            'proof_label' => $this->proof?->label() ?? null,
            'student' => $this->whenLoaded('student', function () {
                return [
                    'id' => $this->student->id,
                    'name' => $this->student->user?->name,
                    'nisn' => $this->student->nisn,
                ];
            }),
            'classroom_student' => $this->whenLoaded('classroomStudent', function () {
                return [
                    'id' => $this->classroomStudent->id,
                    'classroom' => $this->classroomStudent->classroom->name ?? null,
                ];
            }),
            'rfid' => $this->whenLoaded('rfid', function () {
                return [
                    'id' => $this->rfid->id,
                    'rfid_number' => $this->rfid->rfid,
                ];
            }),
        ];
    }
}