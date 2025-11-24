<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TeacherScheduleWithAttendanceResource extends JsonResource
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
                    'duration' => $this->calculateDuration($this->lessonHour->start, $this->lessonHour->end),
                ];
            }),
            'teacher' => $this->whenLoaded('teacher', function () {
                return [
                    'id' => $this->teacher->id,
                    'name' => $this->teacher->user->name,
                ];
            }),
            'attendance' => [
                'status' => $this->attendance_completion_status ?? 'pending',
                'status_label' => $this->getAttendanceStatusLabel($this->attendance_completion_status ?? 'pending'),
                'is_completed' => ($this->attendance_completion_status ?? 'pending') === 'completed',
                'can_cross_check' => $this->whenLoaded('lessonHour', function () {
                return $this->calculateLessonOrder() >= 2;
            }),
                'has_cross_checked' => $this->has_cross_checked ?? false,
                'is_rfid_lesson' => $this->whenLoaded('lessonHour', function () {
                return $this->calculateLessonOrder() === 1;
            }),
                'rfid_completed' => $this->rfid_attendance_completed ?? false,
                'cross_check_available' => $this->cross_check_available ?? false,
                'cross_check_completed' => $this->cross_check_completed ?? false,
            ],
            'classroom_info' => [
                'student_count' => $this->student_count ?? 0,
            ],
            'time_status' => $this->getTimeStatus(),
            'is_current_lesson' => $this->isCurrentLesson(),
            'is_past_lesson' => $this->isPastLesson(),
            'is_upcoming_lesson' => $this->isUpcomingLesson(),
        ];
    }

    private function getAttendanceStatusLabel(string $status): string
    {
        return match($status) {
            'completed' => 'Selesai',
            'pending' => 'Belum Dimulai',
            'cross-check-available' => 'Siap Cross-Check',
            default => 'Tidak Diketahui'
        };
    }

    private function calculateDuration(string $startTime, string $endTime): string
    {
        try {
            $timeFormat = strlen($startTime) <= 5 ? 'H:i' : 'H:i:s';
            $start = \Carbon\Carbon::createFromFormat($timeFormat, $startTime);
            $end = \Carbon\Carbon::createFromFormat($timeFormat, $endTime);

            return $start->diffInMinutes($end) . ' menit';
        } catch (\Exception $e) {
            return 'Tidak diketahui menit';
        }
    }

    private function getTimeStatus(): string
    {
        if (!isset($this->lessonHour)) {
            return 'unknown';
        }

        $now = \Carbon\Carbon::now()->timezone('Asia/Jakarta');

        $timeFormat = strlen($this->lessonHour->start) <= 5 ? 'H:i' : 'H:i:s';

        try {
            $startTime = \Carbon\Carbon::createFromFormat($timeFormat, $this->lessonHour->start);
            $endTime = \Carbon\Carbon::createFromFormat($timeFormat, $this->lessonHour->end);

            if ($now->lt($startTime)) {
                return 'upcoming';
            } elseif ($now->between($startTime, $endTime)) {
                return 'current';
            } else {
                return 'past';
            }
        } catch (\Exception $e) {
            return 'unknown';
        }
    }

    private function isCurrentLesson(): bool
    {
        return $this->getTimeStatus() === 'current';
    }

    private function isPastLesson(): bool
    {
        return $this->getTimeStatus() === 'past';
    }

    private function isUpcomingLesson(): bool
    {
        return $this->getTimeStatus() === 'upcoming';
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