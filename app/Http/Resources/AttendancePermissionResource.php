<?php

namespace App\Http\Resources;

use App\Traits\ResolvesImageUrlTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendancePermissionResource extends JsonResource
{
    use ResolvesImageUrlTrait;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type?->value,
            'type_label' => $this->type?->label(),
            'start_date' => $this->start_date?->format('d-m-Y'),
            'end_date' => $this->end_date?->format('d-m-Y'),
            'reason' => $this->reason,
            'proof' => $this->proof ? $this->resolveImageUrl($this->proof) : null,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'student' => [
                'id' => $this->student->id,
                'name' => $this->student->user?->name,
                'nisn' => $this->student->nisn,
            ],
            'counselor' => $this->formatCounselor(),
            'verification_message' => $this->verificationMessage(),
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
            'verified_at' => $this->verified_at?->format('d-m-Y H:i'),
        ];
    }

    private function verificationMessage(): string
    {
        return match ($this->status?->value) {
            'pending' => 'Menunggu verifikasi oleh konselor',
            'approved' => 'Izin telah disetujui',
            'rejected' => 'Izin telah ditolak',
            default => 'Menunggu verifikasi',
        };
    }
}
