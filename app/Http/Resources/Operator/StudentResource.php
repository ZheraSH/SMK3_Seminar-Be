<?php

namespace App\Http\Resources\Operator;

use App\Enums\StudentStatusEnum;
use App\Traits\ResolvesImageUrlTrait;
use App\Traits\Resources\HasEnumLabelsTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    use ResolvesImageUrlTrait, HasEnumLabelsTrait;

    public function toArray(Request $request): array
    {
        $user = $this->user;

        return [
            'id' => $this->id,
            'name' => $user?->name,
            'email' => $user?->email,
            'image' => $this->resolveImageUrl($this->image),
            'nisn' => $this->nisn,
            'gender_value' => $this->getEnumValue($this->gender),
            'gender_label' => $this->getEnumLabel($this->gender),
            'religion' => $this->religion?->name,
            'birth_date' => $this->birth_date,
            'birth_place' => $this->birth_place,
            'number_kk' => $this->number_kk,
            'number_akta' => $this->number_akta,
            'order_child' => $this->order_child,
            'count_siblings' => $this->count_siblings,
            'address' => $this->address,
            'classroom' => $this->getActiveClassroomData(),
            'rfid' => $this->getRfidData(),
        ];
    }

    private function getActiveClassroomData()
    {
        $activeClassroomStudent = $this->classroomStudents
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->first();

        if (!$activeClassroomStudent) {
            return ['message' => 'Siswa belum memiliki kelas aktif'];
        }

        $activeClassroom = $activeClassroomStudent->classroom;

        return [
            'id' => $activeClassroom->id,
            'name' => $activeClassroom->name,
        ];
    }

    private function getRfidData()
    {
        if ($this->relationLoaded('rfid') && $this->rfid) {
            return [
                'id' => $this->rfid->id,
                'rfid' => $this->rfid->rfid,
            ];
        }

        return ['message' => 'Siswa belum memiliki kartu RFID'];
    }
}
