<?php

namespace App\Http\Resources\Teacher;

use App\Enums\AttendanceStatusEnum;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class TeacherCrossCheckDataResource extends JsonResource
{
    public function toArray($request)
    {
        $lessonSchedule = $this->lesson_schedule;
        $classroom = $this->classroom;
        $studentsData = $this->students ?? [];
        $paginationMeta = $this->pagination ?? null;

        return [
            'date' => $this->date,
            'lesson_order' => $this->lesson_order,
            'classroom' => $classroom ? [
                'id' => $classroom->id,
                'name' => $classroom->name ?? 'Tidak diketahui',
                'level' => $classroom->levelClass->name ?? null,
                'major' => $classroom->major->code ?? null,
                'school_year' => $classroom->schoolYear->name ?? null,
                'homeroom_teacher' => $classroom->homeroomTeacher ? [
                    'id' => $classroom->homeroomTeacher->id,
                    'name' => $classroom->homeroomTeacher->user->name ?? 'Tidak diketahui',
                ] : null,
            ] : null,
            'lesson_schedule' => $lessonSchedule ? [
                'id' => $lessonSchedule->id,
                'day' => $lessonSchedule->day,
                'day_label' => $lessonSchedule->day?->label() ?? null,
                'subject' => $lessonSchedule->subject ? [
                    'id' => $lessonSchedule->subject->id,
                    'name' => $lessonSchedule->subject->name,
                ] : null,
                'teacher' => $lessonSchedule->teacher ? [
                    'id' => $lessonSchedule->teacher->id,
                    'name' => $lessonSchedule->teacher->user->name ?? 'Tidak diketahui',
                ] : null,
                'lesson_hour' => $lessonSchedule->lessonHour ? [
                    'id' => $lessonSchedule->lessonHour->id,
                    'start_time' => $lessonSchedule->lessonHour->start ?? $lessonSchedule->lessonHour->start_time,
                    'end_time' => $lessonSchedule->lessonHour->end ?? $lessonSchedule->lessonHour->end_time,
                    'name' => $lessonSchedule->lessonHour->name ?? "Jam Ke {$this->lesson_order}",
                ] : null,
            ] : null,
            'submission_status' => [
                'has_submitted' => $this->submission_status->has_submitted ?? false,
                'submitted_at' => $this->submission_status->submitted_at ?? null,
                'can_resubmit' => $this->submission_status->can_resubmit ?? true,
            ],
            'students' => array_map(function ($student) {
                $statusLabel = null;
                if ($student['current_status']) {
                    $statusEnum = AttendanceStatusEnum::tryFrom($student['current_status']);
                    $statusLabel = $statusEnum?->label();
                } else {
                    $statusLabel = 'Belum Absen';
                }

                return [
                    'id' => (string) ($student['student_id'] ?? $student['id']),
                    'name' => $student['name'] ?? 'Tidak diketahui',
                    'nisn' => $student['nisn'] ?? null,
                    'is_locked' => $student['is_locked'] ?? false,
                    'attendance_status' => [
                        'code' => $student['current_status'],
                        'label' => $statusLabel,
                        'is_final' => $student['existing_attendance']['is_final'] ?? false,
                    ],
                    'arrival_info' => $student['rfid_info'] ? [
                        'status' => $student['rfid_info']['status'],
                        'checkin_time' => $student['rfid_info']['checkin_time'],
                        'checkout_time' => $student['rfid_info']['checkout_time'],
                    ] : null,
                ];
            }, $studentsData),
            'meta' => $paginationMeta,
        ];
    }
}
