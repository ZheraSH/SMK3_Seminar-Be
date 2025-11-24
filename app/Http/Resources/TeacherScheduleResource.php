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
            'day_label' => $this->day?->label() ?? 'Hari tidak diketahui',
            'lesson_order' => $this->whenLoaded('lessonHour', function () {
                return $this->calculateLessonOrder();
            }),
            'subject' => $this->whenLoaded('subject', function () {
                if ($this->lessonHour && !$this->lessonHour->is_lesson) {
                    return null;
                }
                return [
                    'id' => $this->subject->id,
                    'name' => $this->subject->name,
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
                    'name' => $this->lessonHour->name,
                    'start_time' => $this->lessonHour->start,
                    'end_time' => $this->lessonHour->end,
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

    private function calculateLessonOrder(): ?int
    {
        if (!$this->lessonHour) {
            return null;
        }

        $lessonHours = \App\Models\LessonHour::where('day', $this->day->value)
            ->orderBy('start')
            ->get();

        $position = $lessonHours->search(function ($lessonHour) {
            return $lessonHour->id === $this->lessonHour->id;
        });

        if ($position !== false) {
            $actualOrder = 1;
            foreach ($lessonHours as $index => $hour) {
                if ($index === $position) {
                    return $actualOrder;
                }
                if ($hour->is_lesson) {
                    $actualOrder++;
                }
            }
        }
        return null;
    }
}