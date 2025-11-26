<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class TeacherScheduleResource extends JsonResource
{
    public function toArray($request)
    {
        $lessonOrder = $this->lesson_order ?? $this->calculateLessonOrder();
        
        return [
            'id' => $this->id,
            'subject' => $this->whenLoaded('subject', function () {
                return [
                    'id' => $this->subject->id ?? null,
                    'name' => $this->subject->name ?? 'Tidak ada mata pelajaran',
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
            'lesson_hour' => $this->whenLoaded('lessonHour', function () use ($lessonOrder) {
                if (!$this->lessonHour) {
                    return null;
                }

                return [
                    'id' => $this->lessonHour->id,
                    'order' => $lessonOrder,
                    'start_time' => $this->getStartTime(),
                    'end_time' => $this->getEndTime(),
                    'name' => $this->lessonHour->name ?? "Jam Ke {$lessonOrder}",
                    'duration' => $this->calculateDuration(),
                ];
            }),
            'day' => $this->day,
            'day_label' => $this->day?->label(),
            'lesson_order' => $lessonOrder,
            'can_cross_check' => $lessonOrder >= 2,
            'has_cross_checked' => $this->has_cross_checked ?? false,
            'time_status' => $this->getTimeStatus(),
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

    private function getStartTime(): ?string
    {
        if (!$this->lessonHour) {
            return null;
        }

        return $this->lessonHour->start_time ?? $this->lessonHour->start ?? null;
    }

    private function getEndTime(): ?string
    {
        if (!$this->lessonHour) {
            return null;
        }

        return $this->lessonHour->end_time ?? $this->lessonHour->end ?? null;
    }

    private function calculateDuration(): string
    {
        $startTime = $this->getStartTime();
        $endTime = $this->getEndTime();

        if (!$startTime || !$endTime) {
            return 'Tidak diketahui';
        }

        try {
            $start = Carbon::createFromFormat('H:i:s', $startTime);
            $end = Carbon::createFromFormat('H:i:s', $endTime);
            $duration = $start->diffInMinutes($end);
            return "{$duration} menit";
        } catch (\Exception $e) {
            try {
                $start = Carbon::createFromFormat('H:i', $startTime);
                $end = Carbon::createFromFormat('H:i', $endTime);
                $duration = $start->diffInMinutes($end);
                return "{$duration} menit";
            } catch (\Exception $e) {
                return 'Format waktu tidak valid';
            }
        }
    }

    private function getTimeStatus(): string
    {
        $startTime = $this->getStartTime();
        $endTime = $this->getEndTime();

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