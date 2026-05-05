<?php

namespace App\Http\Resources\Operator;

use App\Traits\Resources\ResolvesImageUrlTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomStudentsResource extends JsonResource
{
    use ResolvesImageUrlTrait;

    public function toArray($request): array
    {
        return [
            'id' => $this->student->id,
            'image' => $this->resolveImageUrl($this->student?->image),
            'name' => $this->student->user->name,
            'nisn' => $this->student->nisn,
            'gender' => $this->student->gender,
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'rfid' => $this->rfidData(),
        ];
    }

    private function rfidData(): array
    {
        if (
            $this->student &&
            $this->student->relationLoaded('rfid') &&
            $this->student->rfid
        ) {
            return [
                'id' => $this->student->rfid->id,
                'rfid' => $this->student->rfid->rfid,
            ];
        }

        return [
            'id' => null,
            'rfid' => null,
            'message' => 'Siswa belum memiliki kartu RFID',
        ];
    }
}