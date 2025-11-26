<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TeacherDashboardOverviewResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'today_date' => $this->resource['today_date'],
            'day_name' => $this->resource['day_name'],
            'total_schedules' => $this->resource['total_schedules'],
            'completed_attendance' => $this->resource['completed_attendance'],
            'pending_attendance' => $this->resource['pending_attendance'],
            'total_classrooms' => $this->resource['total_classrooms'],
            'attendance_completion_rate' => $this->resource['total_schedules'] > 0
                ? round(($this->resource['completed_attendance'] / $this->resource['total_schedules']) * 100, 2)
                : 0,
            'current_schedule' => $this->when($this->resource['current_schedule'], function () {
                return [
                    'classroom_name' => $this->resource['current_schedule']['classroom_name'],
                    'subject_name' => $this->resource['current_schedule']['subject_name'],
                    'start_time' => $this->resource['current_schedule']['start_time'],
                    'end_time' => $this->resource['current_schedule']['end_time'],
                    'lesson_order' => $this->resource['current_schedule']['lesson_order'],
                    'attendance_status' => $this->resource['current_schedule']['attendance_status'],
                    'attendance_status_label' => $this->getAttendanceStatusLabel($this->resource['current_schedule']['attendance_status']),
                ];
            }),
            'next_schedule' => $this->when($this->resource['next_schedule'], function () {
                return [
                    'classroom_name' => $this->resource['next_schedule']['classroom_name'],
                    'subject_name' => $this->resource['next_schedule']['subject_name'],
                    'start_time' => $this->resource['next_schedule']['start_time'],
                    'end_time' => $this->resource['next_schedule']['end_time'],
                    'lesson_order' => $this->resource['next_schedule']['lesson_order'],
                    'attendance_status' => $this->resource['next_schedule']['attendance_status'],
                    'attendance_status_label' => $this->getAttendanceStatusLabel($this->resource['next_schedule']['attendance_status']),
                ];
            }),
            'summary' => [
                'has_schedule_today' => $this->resource['total_schedules'] > 0,
                'has_current_class' => !is_null($this->resource['current_schedule']),
                'has_upcoming_class' => !is_null($this->resource['next_schedule']),
                'all_attendance_completed' => $this->resource['pending_attendance'] === 0,
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
}