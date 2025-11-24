<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TeacherClassroomListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'classroom' => [
                'id' => $this->resource['classroom_id'],
                'name' => $this->resource['classroom_name'],
                'major' => $this->resource['major'],
                'level' => $this->resource['level'],
                'display_name' => $this->resource['classroom_name'] .
                                ($this->resource['major'] ? ' - ' . $this->resource['major'] : '') .
                                ($this->resource['level'] ? ' (' . $this->resource['level'] . ')' : ''),
            ],
            'schedule_info' => [
                'total_lessons' => $this->resource['total_lessons'],
                'completed_lessons' => $this->resource['completed_lessons'],
                'pending_lessons' => $this->resource['total_lessons'] - $this->resource['completed_lessons'],
                'completion_rate' => $this->resource['total_lessons'] > 0
                    ? round(($this->resource['completed_lessons'] / $this->resource['total_lessons']) * 100, 2)
                    : 0,
                'first_lesson_time' => $this->resource['first_lesson_time'],
                'last_lesson_time' => $this->resource['last_lesson_time'],
                'time_range' => $this->resource['first_lesson_time'] . ' - ' . $this->resource['last_lesson_time'],
            ],
            'students' => [
                'total_count' => $this->resource['student_count'],
            ],
            'subjects' => collect($this->resource['subjects'])->map(function ($subject) {
                return [
                    'name' => $subject['name'],
                    'lesson_order' => $subject['lesson_order'],
                    'time' => $subject['time'],
                    'start_time' => explode('-', $subject['time'])[0],
                    'end_time' => explode('-', $subject['time'])[1],
                    'attendance_status' => $subject['attendance_status'],
                    'attendance_status_label' => $this->getAttendanceStatusLabel($subject['attendance_status']),
                    'is_completed' => $subject['attendance_status'] === 'completed',
                    'can_cross_check' => $subject['lesson_order'] >= 2,
                    'is_first_lesson' => $subject['lesson_order'] === 1,
                ];
            }),
            'attendance_summary' => [
                'rfid_completed' => $this->resource['attendance_summary']['rfid_completed'],
                'cross_check_completed' => $this->resource['attendance_summary']['cross_check_completed'],
                'total_cross_check_available' => $this->resource['attendance_summary']['total_cross_check_available'],
                'cross_check_completion_rate' => $this->resource['attendance_summary']['total_cross_check_available'] > 0
                    ? round(($this->resource['attendance_summary']['cross_check_completed'] /
                            $this->resource['attendance_summary']['total_cross_check_available']) * 100, 2)
                    : 0,
                'has_pending_actions' => !$this->resource['attendance_summary']['rfid_completed'] ||
                    ($this->resource['attendance_summary']['total_cross_check_available'] >
                     $this->resource['attendance_summary']['cross_check_completed']),
            ],
            'status' => [
                'is_fully_completed' => $this->resource['attendance_summary']['rfid_completed'] &&
                    ($this->resource['attendance_summary']['total_cross_check_available'] ===
                     $this->resource['attendance_summary']['cross_check_completed']),
                'needs_rfid' => !$this->resource['attendance_summary']['rfid_completed'],
                'needs_cross_check' => $this->resource['attendance_summary']['total_cross_check_available'] >
                    $this->resource['attendance_summary']['cross_check_completed'],
                'status_label' => $this->getClassroomStatusLabel(),
            ]
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

    private function getClassroomStatusLabel(): string
    {
        $rfidCompleted = $this->resource['attendance_summary']['rfid_completed'];
        $crossCheckCompleted = $this->resource['attendance_summary']['cross_check_completed'];
        $totalCrossCheckAvailable = $this->resource['attendance_summary']['total_cross_check_available'];

        if (!$rfidCompleted) {
            return 'Menunggu RFID';
        } elseif ($totalCrossCheckAvailable > 0 && $crossCheckCompleted < $totalCrossCheckAvailable) {
            return 'Perlu Cross-Check';
        } elseif ($totalCrossCheckAvailable > 0 && $crossCheckCompleted === $totalCrossCheckAvailable) {
            return 'Selesai';
        } else {
            return 'Selesai (RFID Only)';
        }
    }
}