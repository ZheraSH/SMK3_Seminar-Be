<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'major' => $this->major?->name,
            'level_class' => $this->levelClass?->name,
            'school_year' => $this->schoolYear?->name,
            'teacher' => $this->teacher?->user?->name,
            'teacher_id' => $this->teacher_id,
            'total_students' => $this->whenLoaded('classroomStudents', function() {
                return $this->classroomStudents->where('status', \App\Enums\StudentStatusEnum::ACTIVE)->count();
            }, 0),
            'total_schedules' => $this->whenLoaded('lessonSchedules', function() {
                return $this->lessonSchedules->count();
            }, 0),
        ];
    }
}   