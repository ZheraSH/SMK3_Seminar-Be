<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TeacherScheduleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'day' => $this->day,
            'day_label' => \App\Enums\DayEnum::from($this->day)->label(),
            'lesson_order' => $this->lesson_order,
            'subject' => $this->whenLoaded('subject', function () {
                return [
                    'id' => $this->subject->id,
                    'name' => $this->subject->name,
                    'code' => $this->subject->code,
                ];
            }),
            'classroom' => $this->whenLoaded('classroom', function () {
                return [
                    'id' => $this->classroom->id,
                    'name' => $this->classroom->name,
                    'major' => $this->classroom->major->code ?? null,
                    'level' => $this->classroom->levelClass->name ?? null,
                ];
            }),
            'lesson_hour' => $this->whenLoaded('lessonHour', function () {
                return [
                    'id' => $this->lessonHour->id,
                    'start_time' => $this->lessonHour->start_time,
                    'end_time' => $this->lessonHour->end_time,
                ];
            }),
            'teacher' => $this->whenLoaded('teacher', function () {
                return [
                    'id' => $this->teacher->id,
                    'name' => $this->teacher->user->name,
                ];
            }),
            'can_cross_check' => $this->can_cross_check ?? false,
            'has_cross_checked' => $this->has_cross_checked ?? false,
        ];
    }
}