<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TeacherClassroomResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'major' => $this->major->code ?? null,
            'level' => $this->levelClass->name ?? null,
            'school_year' => $this->schoolYear->name ?? '2024/2025',
            'homeroom_teacher' => $this->teacher ? [
                'id' => $this->teacher->id,
                'name' => $this->teacher->user->name ?? 'Tidak diketahui',
                'type' => 'homeroom_teacher',
                'type_label' => 'Wali Kelas',
            ] : null,
        ];
    }
}