<?php

namespace App\Http\Resources\Teacher;

use App\Traits\Resources\HasEnumLabelsTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherClassroomResource extends JsonResource
{
    use HasEnumLabelsTrait;
    public function toArray($request)
    {
        $classroom = $this->classroom;
        $schedule = $this->first_schedule;

        return [
            'id' => $classroom->id,
            'name' => $classroom->name,
            'school_year' => $classroom->schoolYear?->name,
            'day' => [
                'value' => $this->getEnumValue($schedule->day),
                'label' => $this->getEnumLabel($schedule->day),
            ],
            'homeroom_teacher' => $classroom->homeroomTeacher ? [
                'id' => $classroom->homeroomTeacher->id,
                'name' => $classroom->homeroomTeacher->user->name ?? null,
            ] : null,
            'students' => [
                'total' => $classroom->classroomStudents?->count() ?? 0,
            ],
        ];
    }
}