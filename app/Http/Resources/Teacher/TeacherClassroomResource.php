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
            'lesson' => [
                'order' => $schedule->lessonHour?->order,
                'order_display' => $schedule->lesson_order_display ?? $schedule->lessonHour?->order,
                'date' => $this->date,
            ],
            'has_cross_checked' => (bool) ($schedule->has_cross_checked ?? false),
            'students' => [
                'total' => $classroom->classroomStudents?->count() ?? 0,
            ],
        ];
    }
}
