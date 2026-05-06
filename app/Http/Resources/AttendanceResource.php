<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\AttendanceStatusEnum;

class AttendanceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'date' => $this->date?->format('d-m-Y'),
            'lesson_order' => $this->lesson_order,
            'status' => $this->status instanceof AttendanceStatusEnum
                ? $this->status->value
                : $this->status,
            'status_label' => $this->status instanceof AttendanceStatusEnum
                ? $this->status->label()
                : (AttendanceStatusEnum::tryFrom($this->status)?->label() ?? $this->status),
            'is_locked' => $this->is_locked,
            'is_final' => $this->is_final,
            'student' => $this->whenLoaded('student', function () {
                return [
                    'id' => $this->student->id,
                    'name' => $this->student->user->name,
                    'nisn' => $this->student->nisn,
                ];
            }),
            'classroom' => $this->whenLoaded('classroomStudent', function () {
                return [
                    'id' => $this->classroomStudent->classroom->id ?? null,
                    'name' => $this->classroomStudent->classroom->name ?? null,
                ];
            }),
            'subject' => $this->whenLoaded('subject', function () {
                return [
                    'id' => $this->subject->id,
                    'name' => $this->subject->name,
                ];
            }),
            'teacher' => $this->whenLoaded('teacher', function () {
                return [
                    'id' => $this->teacher->id,
                    'name' => $this->teacher->user->name ?? null,
                ];
            }),
        ];
    }
}