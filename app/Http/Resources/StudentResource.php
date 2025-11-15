<?php

namespace App\Http\Resources;

use App\Enums\RoleEnum;
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
            'birth_date' => $this->birth_date,
            'birth_place' => $this->birth_place,
            'address' => $this->address,
            'status' => $this->status?->label(),
            'classroom' => $activeClassroom ? [
                'name' => $activeClassroom->name,
                'major' => $activeClassroom->major?->code,
                'level_class' => $activeClassroom->levelClass?->name,
                'school_year' => $activeClassroom->schoolYear?->name,
            ] : [
                'message' => 'Siswa belum memiliki kelas aktif'
            ],
            'roles' => $user?->roles?->map(function ($role) {
                return [
                    'value' => $role->name,
                    'label' => RoleEnum::tryFrom($role->name)?->label() ?? $role->name,
                ];
            }) ?? []
        ];
    }

    private function getActiveClassroom()
    {
        if (!$this->relationLoaded('classroomStudents')) {
            return null;
        }

        return $this->classroomStudents
            ->where('status', \App\Enums\StudentStatusEnum::ACTIVE->value)
            ->first()
            ?->classroom;
    }
}