<?php

namespace App\Http\Resources;

use App\Enums\StudentStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->user;
        $activeClassroom = $this->getActiveClassroom();

        return [
            'id' => $this->id,
            'name' => $user?->name,
            'email' => $user?->email,
            'nisn' => $this->nisn,
            'gender' => $this->gender?->label(),
            'religion' => $this->religion?->name,
            'birth_date' => $this->birth_date,
            'birth_place' => $this->birth_place,
            'number_kk' => $this->number_kk,
            'number_akta' => $this->number_akta,
            'order_child' => $this->order_child,
            'count_siblings' => $this->count_siblings,
            'address' => $this->address,
            'classroom' => $activeClassroom ? [
                'id' => $activeClassroom->id,
                'name' => $activeClassroom->name,
                'major' => $activeClassroom->major?->code,
                'level_class' => $activeClassroom->levelClass->name,
                ] : [
                'message' => 'Siswa belum memiliki kelas aktif'
            ],
            'rfid' => $this->whenLoaded('rfid', function () {
                if ($this->rfid) {
                    return [
                        'id' => $this->rfid->id,
                        'rfid' => $this->rfid->number,
                    ];
                }
                return null;
            }, null),
        ];
    }

    private function getActiveClassroom()
    {
        $activeClassroomStudent = $this->classroomStudents
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->first();

        return $activeClassroomStudent?->classroom;
    }
}