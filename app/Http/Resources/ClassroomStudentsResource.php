<?php

namespace App\Http\Resources;

use App\Enums\StudentStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomStudentsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student' => [
                'id' => $this->student->id,
                'name' => $this->student->user->name,
                'email' => $this->student->user->email,
                'nisn' => $this->student->nisn,
                'gender' => $this->student->gender?->label() ?? 'Tidak diketahui',
            ],
            'status' => $this->status->label(),
            'classroom' => $this->getActiveClassroomData(),
            'rfid' => $this->getRfidData(),
        ];
    }

    private function getActiveClassroomData()
    {
        if (!$this->relationLoaded('student') || !$this->student->relationLoaded('classroomStudents')) {
            return ['message' => 'Relasi student.classroomStudents belum diload'];
        }

        $activeClassroomStudent = $this->student->classroomStudents
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->first();

        if (!$activeClassroomStudent) {
            return ['message' => 'Siswa belum memiliki kelas aktif'];
        }

        $activeClassroom = $activeClassroomStudent->classroom;

        return [
            'id' => $activeClassroom->id,
            'name' => $activeClassroom->name,
            'major' => $activeClassroom->major->code,
            'level_class' => $activeClassroom->levelClass->name,
            'schoolyear' => $activeClassroom->schoolyear->name,
        ];
    }
    private function getRfidData()
    {
        if (
            $this->relationLoaded('student')
            && $this->student->relationLoaded('rfid')
            && $this->student->rfid
        ) {
            return [
                'id' => $this->student->rfid->id,
                'rfid' => $this->student->rfid->rfid,
            ];
        }

        return ['message' => 'Siswa belum memiliki kartu RFID'];
    }
}