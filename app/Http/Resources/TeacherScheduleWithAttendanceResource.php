<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class TeacherScheduleWithAttendanceResource extends JsonResource
{
    public function toArray($request)
    {
        $lessonOrder = $this->lesson_order ?? $this->calculateLessonOrder();
        
        return [
            'id' => $this->id,
            'day' => $this->day,
            'day_label' => $this->day?->label(),
            'lesson_order' => $lessonOrder,
            'subject' => $this->whenLoaded('subject', function () {
                if ($this->lessonHour && !$this->lessonHour->is_lesson) {
                    return null;
                }
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
            'lesson_hour' => $this->whenLoaded('lessonHour', function () {
                if (!$this->lessonHour) {
                    return null;
                }
                return [
                    'id' => $this->lessonHour->id,
                    'name' => $this->lessonHour->name,
                    'start_time' => $this->lessonHour->start ?? $this->lessonHour->start_time,
                    'end_time' => $this->lessonHour->end ?? $this->lessonHour->end_time,
                    'duration' => $this->calculateDuration(),
                ];
            }),
            'teacher' => $this->whenLoaded('employee', function () {
                return [
                    'id' => $this->employee->id ?? $this->teacher->id ?? null,
                    'name' => $this->employee->user->name ?? $this->teacher->user->name ?? 'Tidak diketahui',
                ];
            }),
            'attendance' => [
                'status' => $this->attendance_completion_status ?? 'pending',
                'status_label' => $this->getAttendanceStatusLabel($this->attendance_completion_status ?? 'pending'),
                'is_completed' => ($this->attendance_completion_status ?? 'pending') === 'completed',
                'can_cross_check' => $lessonOrder >= 2,
                'has_cross_checked' => $this->has_cross_checked ?? false,
                'is_rfid_lesson' => $lessonOrder === 1,
                'rfid_completed' => $this->rfid_attendance_completed ?? false,
                'cross_check_available' => $this->cross_check_available ?? false,
                'cross_check_completed' => $this->cross_check_completed ?? false,
            ],
            'classroom_info' => [
                'student_count' => $this->student_count ?? 0,
            ],
            'time_status' => $this->getTimeStatus(),
            'is_current_lesson' => $this->getTimeStatus() === 'current',
            'is_past_lesson' => $this->getTimeStatus() === 'past',
            'is_upcoming_lesson' => $this->getTimeStatus() === 'upcoming',
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

    private function calculateDuration(): string
    {
        $startTime = $this->lessonHour->start ?? $this->lessonHour->start_time ?? null;
        $endTime = $this->lessonHour->end ?? $this->lessonHour->end_time ?? null;

        if (!$startTime || !$endTime) {
            return 'Tidak diketahui';
        }

        try {
            $start = Carbon::createFromFormat('H:i:s', $startTime);
            $end = Carbon::createFromFormat('H:i:s', $endTime);
            return $start->diffInMinutes($end) . ' menit';
        } catch (\Exception $e) {
            try {
                $start = Carbon::createFromFormat('H:i', $startTime);
                $end = Carbon::createFromFormat('H:i', $endTime);
                return $start->diffInMinutes($end) . ' menit';
            } catch (\Exception $e) {
                return 'Format waktu tidak valid';
            }
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

            // Set tanggal ke hari ini untuk perbandingan
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

    private function calculateLessonOrder(): int
    {
        if (!$this->lessonHour) {
            return 1;
        }

        // Jika lessonHour memiliki field order, gunakan itu
        if (isset($this->lessonHour->order) && !is_null($this->lessonHour->order)) {
            return $this->lessonHour->order;
        }

        // Jika tidak, hitung manual berdasarkan hari
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
}