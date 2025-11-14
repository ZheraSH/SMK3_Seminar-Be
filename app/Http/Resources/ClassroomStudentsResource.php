<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomStudentsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $activeStudents = $this->classroomStudents
            ->where('status', \App\Enums\StudentStatusEnum::ACTIVE);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'homeroom_teacher' => $this->teacher?->user?->name,
            'total_students' => $activeStudents->count(),
            'students' => $activeStudents->map(function ($classroomStudent) {
                return [
                    'id' => $classroomStudent->student->id,
                    'name' => $classroomStudent->student->user->name,
                    'nisn' => $classroomStudent->student->nisn,
                    'status' => $classroomStudent->status->label(),
                ];
            })->values(),
        ];
    }
}