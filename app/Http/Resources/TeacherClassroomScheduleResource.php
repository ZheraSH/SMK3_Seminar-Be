<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class TeacherClassroomScheduleResource extends JsonResource
{
    public function toArray($request)
    {
        $lessonOrder = $this->lesson_order ?? $this->calculateLessonOrder();
        
        return [
            'id' => $this->id,
            'day' => $this->day,
            'day_label' => $this->day?->label(),
            'classroom' => $this->whenLoaded('classroom', function () {
                return [
                    'id' => $this->classroom->id,
                    'name' => $this->classroom->name,
                    'major' => $this->classroom->major->code ?? null,
                    'level' => $this->classroom->levelClass->name ?? null,
                    'schoolYear' => $this->classroom->schoolYear->name ?? '2024/2025',
                    'homeroom_teacher' => $this->classroom->employee ? [
                        'id' => $this->classroom->employee->id ?? null,
                        'name' => $this->classroom->employee->user->name ?? 'Tidak diketahui'
                    ] : null
                ];
            }),
            'teacher' => $this->whenLoaded('employee', function () {
                return [
                    'id' => $this->employee->id ?? null,
                    'name' => $this->employee->user->name ?? 'Tidak diketahui',
                ];
            }),
            'subject' => $this->whenLoaded('subject', function () {
                return [
                    'id' => $this->subject->id ?? null,
                    'name' => $this->subject->name ?? 'Tidak ada mata pelajaran',
                ];
            }),
            'lesson_order' => $lessonOrder,
            'can_cross_check' => $lessonOrder >= 2,
            'has_cross_checked' => $this->has_cross_checked ?? false,
            'time_status' => $this->getTimeStatus(),
            'lesson_hour' => $this->whenLoaded('lessonHour', function () {
                if (!$this->lessonHour) {
                    return null;
                }
                return [
                    'start_time' => $this->lessonHour->start ?? $this->lessonHour->start_time,
                    'end_time' => $this->lessonHour->end ?? $this->lessonHour->end_time,
                ];
            }),
        ];
    }

    private function calculateLessonOrder(): int
    {
        if (!$this->lessonHour) {
            return 1;
        }

        if (isset($this->lessonHour->order) && !is_null($this->lessonHour->order)) {
            return $this->lessonHour->order;
        }

        try {
            $lessonHours = \App\Models\LessonHour::where('day', $this->day)
                ->where('is_lesson', true)
                ->orderBy('start')
                ->get();

            $position = $lessonHours->search(function ($lessonHour) {
                return $lessonHour->id === $this->lessonHour->id;
            });

            return $position !== false ? $position + 1 : 1;
        } catch (\Exception $e) {
            return 1;
        }
    }

    private function getTimeStatus(): string
    {
        if (!isset($this->lessonHour)) {
            return 'unknown';
        }

        $startTime = $this->lessonHour->start ?? $this->lessonHour->start_time ?? null;
        $endTime = $this->lessonHour->end ?? $this->lessonHour->end_time ?? null;

        if (!$startTime || !$endTime) {
            return 'unknown';
        }

        $now = Carbon::now()->timezone('Asia/Jakarta');

        try {
            $timeFormat = strlen($startTime) <= 5 ? 'H:i' : 'H:i:s';
            $start = Carbon::createFromFormat($timeFormat, $startTime);
            $end = Carbon::createFromFormat($timeFormat, $endTime);
            
            $start = $start->setDate($now->year, $now->month, $now->day);
            $end = $end->setDate($now->year, $now->month, $now->day);

            if ($now->lt($start)) {
                return 'upcoming';
            } elseif ($now->between($start, $end)) {
                return 'current';
            } else {
                return 'past';
            }
        } catch (\Exception $e) {
            return 'unknown';
        }
    }
}