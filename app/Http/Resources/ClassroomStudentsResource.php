<?php

namespace App\Http\Resources;

use App\Enums\StudentStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomStudentsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $activeClassroomData = $this->getActiveClassroomData();

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
            'HomeroomTeacher' => $activeClassroomData['HomeroomTeacher'],
            'classroom' => $activeClassroomData['classroom'],
            'rfid' => $this->getRfidData(),
            'total_students' => $activeClassroomData['total_students'],
        ];
    }

    private function getActiveClassroomData(): array
    {
        if (!$this->relationLoaded('student') || !$this->student->relationLoaded('classroomStudents')) {
            return [
                'classroom' => ['message' => 'Relasi student.classroomStudents belum diload'],
                'HomeroomTeacher' => null,
                'total_students' => 0
            ];
        }

        $activeClassroomStudent = $this->student->classroomStudents
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->first();

        if (!$activeClassroomStudent) {
            return [
                'classroom' => ['message' => 'Siswa belum memiliki kelas aktif'],
                'HomeroomTeacher' => null,
                'total_students' => 0
            ];
        }

        $classroom = $activeClassroomStudent->classroom;

        return [
            'classroom' => [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'major' => $classroom->major->code,
                'level_class' => $classroom->levelClass->name,
                'schoolyear' => $classroom->schoolyear->name,
            ],
            'HomeroomTeacher' => $classroom->homeroomTeacher ? [
                'id' => $classroom->homeroomTeacher->id,
                'name' => $classroom->homeroomTeacher->user->name,
            ] : null,
            'total_students' => $classroom->classroomStudents
                ->where('status', StudentStatusEnum::ACTIVE->value)
                ->count(),
        ];
    }

    private function getRfidData(): array
    {
        if (
            $this->relationLoaded('student') &&
            $this->student->relationLoaded('rfid') &&
            $this->student->rfid
        ) {
            return [
                'id' => $this->student->rfid->id,
                'rfid' => $this->student->rfid->rfid,
            ];
        }

        return ['message' => 'Siswa belum memiliki kartu RFID'];
    }
}