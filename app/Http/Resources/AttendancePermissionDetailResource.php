<?php

namespace App\Http\Resources;

use App\Enums\StudentStatusEnum;
use App\Traits\ResolvesImageUrlTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendancePermissionDetailResource extends JsonResource
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
            ],
            'classroom' => $this->getActiveClassroomData(),
            'counselor' => $this->formatCounselor(),
            'verification_message' => $this->verificationMessage(),
            'verification' => [
                'status' => $this->status?->value,
                'status_label' => $this->status?->label(),
                'verified_by' => $this->counselor?->user?->name ?? 'Belum diverifikasi',
                'verified_at' => $this->verified_at?->format('d-m-Y H:i:s'),
            ],
        ];
    }

    private function getActiveClassroomData()
    {
        $activeClassroomStudent = $this->student
            ->classroomStudents
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->first();
    
        if (!$activeClassroomStudent) {
            return ['message' => 'Siswa belum memiliki kelas aktif'];
        }
    
        $activeClassroom = $activeClassroomStudent->classroom;
    
        return [
            'id' => $activeClassroom->id,
            'name' => $activeClassroom->name,
        ];
    }
    
    private function formatCounselor(): array
    {
        if (!$this->counselor_id) {
            return [
                'message' => 'Menunggu verifikasi oleh konselor',
                'status' => 'pending'
            ];
        }

        return [
            'id' => $this->counselor->id,
            'name' => $this->counselor->user?->name,
            'email' => $this->counselor->user?->email,
            'verified_at' => $this->verified_at?->format('d-m-Y H:i:s'),
        ];
    }

    private function verificationMessage(): string
    {
        return match ($this->status?->value) {
            'pending' => 'Izin Anda sedang menunggu verifikasi oleh konselor',
            'approved' => 'Izin Anda telah disetujui oleh konselor',
            'rejected' => 'Izin Anda telah ditolak oleh konselor',
            default => 'Menunggu verifikasi oleh konselor',
        };
    }
}
