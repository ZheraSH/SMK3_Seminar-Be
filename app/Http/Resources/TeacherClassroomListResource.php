<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TeacherClassroomListResource extends JsonResource
{
    public function toArray($request)
    {
        $classroom = $this->resource['classroom'];
        $schedules = $this->resource['schedules'];
        $attendanceSummary = $this->resource['attendance_summary'];
        $studentCount = $this->resource['student_count'];

        return [
            'classroom' => [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'school_year' => $classroom->schoolYear->name ?? '2024/2025',
                'homeroom_teacher' => $this->getHomeroomTeacher($classroom),
            ],
            'students' => [
                'total_count' => $studentCount,
            ],
            'subjects' => collect($schedules)->map(function ($schedule) {
                return [
                    'attendance_status' => $schedule['attendance_status'],
                    'attendance_status_label' => $this->getAttendanceStatusLabel($schedule['attendance_status']),
                    'is_completed' => $schedule['attendance_status'] === 'completed',
                    'can_cross_check' => $schedule['lesson_order'] >= 2,
                    'is_first_lesson' => $schedule['lesson_order'] === 1,
                ];
            }),
            'status' => [
                'is_fully_completed' => $attendanceSummary['is_fully_completed'],
                'needs_rfid' => !$attendanceSummary['rfid_completed'],
                'needs_cross_check' => $attendanceSummary['total_cross_check_available'] > $attendanceSummary['cross_check_completed'],
                'status_label' => $this->getClassroomStatusLabel($attendanceSummary),
            ]
        ];
    }

    private function getHomeroomTeacher($classroom): ?array
    {
        if ($classroom->teacher && $classroom->teacher->user) {
            return [
                'id' => $classroom->teacher->id,
                'name' => $classroom->teacher->user->name,
                'type' => 'homeroom_teacher',
                'type_label' => 'Wali Kelas',
            ];
        }
        return null;
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

    private function getClassroomStatusLabel(array $attendanceSummary): string
    {
        $rfidCompleted = $attendanceSummary['rfid_completed'];
        $crossCheckCompleted = $attendanceSummary['cross_check_completed'];
        $totalCrossCheckAvailable = $attendanceSummary['total_cross_check_available'];

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