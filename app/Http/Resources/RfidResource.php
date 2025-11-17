<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RfidResource extends JsonResource
{
    public function toArray($request)
    {
        $student = $this->whenLoaded('student');

        return [
            'id' => $this->id,
            'rfid' => $this->rfid,
            'status' => $this->status?->value ?? $this->status,
            'status_label' => $this->status?->label() ?? $this->status,
            'student' => $student ? [
                'id' => $this->student->id,
                'name' => $this->student->user->name ?? 'Nama tidak tersedia',
                'nisn' => $this->student->nisn,
                'status' => $this->student->status?->value,
                'status_label' => $this->student->status?->label(),
            ] : null,
        ];
    }
}