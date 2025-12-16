<?php

namespace App\Http\Resources;

use App\Enums\StudentStatusEnum;
use App\Traits\ResolvesImageUrlTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    use ResolvesImageUrlTrait;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'image' => $this->resolveImageUrl($this->image),
            'address' => $this->address,
            'nisn' => $this->nisn,
            'number_kk' => $this->number_kk,
            'number_akta' => $this->number_akta,
            'gender' => $this->gender ? [
                'value' => $this->gender->value,
                'label' => $this->gender->label(),
            ] : null,
            'religion' => $this->religion ? [
                'id' => $this->religion->id,
                'name' => $this->religion->name,
            ] : null,
            'birth_place' => $this->birth_place,
            'birth_date' => $this->birth_date,
            'order_child' => $this->order_child,
            'count_siblings' => $this->count_siblings,
            'classroom' => $this->activeClassroom(),
            'rfid' => $this->rfidData(),
        ];
    }

    private function activeClassroom(): ?array
    {
        $active = $this->classroomStudents
            ->firstWhere('status', StudentStatusEnum::ACTIVE->value);

        if (!$active || !$active->classroom) {
            return null;
        }

        $classroom = $active->classroom;

        return [
            'id' => $classroom->id,
            'name' => $classroom->name,
            'school_year' => $classroom->schoolYear?->name,
        ];
    }

    private function rfidData(): ?array
    {
        if (!$this->relationLoaded('rfid') || !$this->rfid) {
            return null;
        }

        return [
            'id' => $this->rfid->id,
            'code' => $this->rfid->rfid,
        ];
    }
}