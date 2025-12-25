<?php

namespace App\Http\Resources;

use App\Enums\StudentStatusEnum;
use App\Traits\Resources\HasEnumLabelsTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendancePermissionResource extends JsonResource
{
    use HasEnumLabelsTrait;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => [
                'value' => $this->type?->value,
                'label' => $this->type?->label(),
            ],
            'status' => [
                'value' =>$this->status?->value,
                'label' => $this->status?->label(),
            ],
            'reason' => $this->reason,
            'counselor' => $this->formatCounselor(),
        ];
    }

    private function formatCounselor(): array
    {
        if (!$this->counselor_id) {
            return [
                'message' => 'Menunggu verifikasi oleh konselor'
            ];
        }

        return [
            'id' => $this->counselor->id,
            'name' => $this->counselor->user?->name,
            'verified_at' => $this->verified_at?->format('d-M-Y'),
        ];
    }
}
