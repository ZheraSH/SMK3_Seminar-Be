<?php

namespace App\Http\Resources;

use App\Enums\StudentStatusEnum;
use App\Traits\Resources\HasEnumLabelsTrait;
use App\Traits\Resources\ResolvesImageUrlTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendancePermissionDetailResource extends JsonResource
{
    use ResolvesImageUrlTrait, HasEnumLabelsTrait;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'proof' => $this->proof ? $this->resolveImageUrl($this->proof) : null,
            'student' => [
                'id' => $this->student->id,
                'name' => $this->student->user?->name,
                'image' => $this->student->image ? $this->resolveImageUrl($this->student->image) : null,
            ],
            'classroom' => $this->getActiveClassroomData(),
            'type' => [
                'value' => $this->type?->value,
                'label' => $this->type?->label(),
            ],
            'date' => [
                'start' => $this->start_date?->format('d-M-Y'),
                'end' => $this->end_date?->format('d-M-Y'),
            ],
            'counselor' => $this->formatCounselor(),
            'status' => [
                'value' =>$this->status?->value,
                'label' => $this->status?->label(),
            ],
            'reason' => $this->reason,
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
            'verified_at' => $this->verified_at?->format('d-M-Y'),
        ];  
    }
}
