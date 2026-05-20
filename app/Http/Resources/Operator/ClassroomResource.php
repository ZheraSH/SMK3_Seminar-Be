<?php

namespace App\Http\Resources\Operator;

use App\Enums\StudentStatusEnum;
use App\Traits\Resources\HasEnumLabelsTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomResource extends JsonResource
{
    use HasEnumLabelsTrait;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'major' => $this->major ? [
                'name' => $this->major->name,
                'code' => $this->major->code,
            ] : null,
            'school_year' => $this->schoolYear?->name,
            'homeroom_teacher' => $this->homeroomTeacher?->user?->name,
            'total_students' => $this->whenLoaded(
                'classroomStudents',
                fn () => $this->activeStudentCount(),
                0
            ),
        ];
    }

    private function activeStudentCount(): int
    {
        return $this->classroomStudents
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->count();
    }
}
