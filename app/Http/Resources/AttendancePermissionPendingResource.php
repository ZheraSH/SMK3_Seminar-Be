<?php

namespace App\Http\Resources;

use App\Enums\StudentStatusEnum;
use App\Traits\Resources\HasEnumLabelsTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendancePermissionPendingResource extends JsonResource
{
    use HasEnumLabelsTrait;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'student' => [
                'id' => $this->student->id,
                'name' => $this->student->user?->name,
            ],
            'classroom' => $this->getActiveClassroomData(),
            'date' => [
                'start' => $this->start_date?->format('d-M-Y'),
                'end' => $this->end_date?->format('d-M-Y'),
            ],
            'type' => [
                'value' => $this->type?->value,
                'label' => $this->type?->label(),
            ],
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
}
