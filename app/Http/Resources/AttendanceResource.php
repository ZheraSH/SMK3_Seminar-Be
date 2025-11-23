<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\AttendanceStatusEnum;
use App\Enums\AttendanceProofEnum;

class AttendanceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'date' => $this->date->format('d-m-Y'),
            'checkin_time' => $this->checkin_time,
            'checkout_time' => $this->checkout_time,
            'lesson_order' => $this->lesson_order,
            'attendance_type' => $this->attendance_type,
            'attendance_type_label' => $this->attendance_type === 'rfid' ? 'RFID' : 'Cross-Check',
            'status' => $this->status,
            'status_label' => AttendanceStatusEnum::from($this->status)->label(),
            'proof' => $this->proof,
            'proof_label' => AttendanceProofEnum::from($this->proof)->label(),
            'student' => $this->whenLoaded('student', function () {
                return [
                    'id' => $this->student->id,
                    'name' => $this->student->user->name,
                    'nisn' => $this->student->nisn,
                ];
            }),
            'classroom' => $this->whenLoaded('classroomStudent', function () {
                return [
                    'id' => $this->classroomStudent->classroom->id ?? null,
                    'name' => $this->classroomStudent->classroom->name ?? null,
                ];
            }),
            'subject' => $this->whenLoaded('subject', function () {
                return [
                    'id' => $this->subject->id,
                    'name' => $this->subject->name,
                ];
            }),
            'teacher' => $this->whenLoaded('teacher', function () {
                return [
                    'id' => $this->teacher->id,
                    'name' => $this->teacher->user->name ?? null,
                ];
            }),
        ];
    }
}