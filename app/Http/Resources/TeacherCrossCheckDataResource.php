<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TeacherCrossCheckDataResource extends JsonResource
{
    public function toArray($request)
    {
        $lessonSchedule = $this->lesson_schedule;
        $classroom = $this->classroom;

        return [
            'date' => $this->date,
            'lesson_order' => $this->lesson_order,
            'classroom' => $classroom ? [
                'id' => $classroom->id,
                'name' => $classroom->name ?? 'Tidak diketahui',
                'level' => $classroom->levelClass->name ?? null,
                'major' => $classroom->major->code ?? null,
                'school_year' => $classroom->schoolYear->name ?? null,
                'homeroom_teacher' => $classroom->teacher ? [
                    'id' => $classroom->teacher->id,
                    'name' => $classroom->teacher->user->name ?? 'Tidak diketahui',
                    'type' => 'homeroom_teacher',
                    'type_label' => 'Wali Kelas',
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
                // TEACHER dari lesson_schedule (guru pengajar)
                'teacher' => $lessonSchedule->employee ? [
                    'id' => $lessonSchedule->employee->id,
                    'name' => $lessonSchedule->employee->user->name ?? 'Tidak diketahui',
                ] : null,
                'lesson_hour' => $lessonSchedule->lessonHour ? [
                    'id' => $lessonSchedule->lessonHour->id,
                    'start_time' => $lessonSchedule->lessonHour->start ?? $lessonSchedule->lessonHour->start_time,
                    'end_time' => $lessonSchedule->lessonHour->end ?? $lessonSchedule->lessonHour->end_time,
                    'name' => $lessonSchedule->lessonHour->name ?? "Jam Ke {$this->lesson_order}",
                ] : null,
            ] : null,
            'summary' => [
                'total_students' => $this->total_students ?? 0,
                'present' => $this->present ?? 0,
                'late' => $this->late ?? 0,
                'alpha' => $this->alpha ?? 0,
                'leave' => $this->leave ?? 0,
                'sick' => $this->sick ?? 0,
            ],
            'students' => $this->students ? array_map(function ($student) {
                return [
                    'id' => $student['student_id'] ?? null,
                    'name' => $student['name'] ?? 'Tidak diketahui',
                    'nisn' => $student['nisn'] ?? null,
                    'existing_attendance' => $student['existing_attendance'] ? [
                        'id' => $student['existing_attendance']['id'] ?? null,
                        'status' => $student['existing_attendance']['status'] ?? null,
                        'status_label' => $student['existing_attendance']['status_label'] ?? null,
                    ] : null,
                ];
            }, $this->students) : [],
        ];
    }
}