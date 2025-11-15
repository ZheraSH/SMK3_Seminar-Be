<?php

namespace App\Http\Resources;

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
                'nisn' => $this->student->nisn,
                'email' => $this->student->user->email,
                'gender' => $this->student->gender?->label() ?? 'Tidak diketahui',
            ],
            'classroom' => [
                'id' => $this->classroom->id,
                'name' => $this->classroom->name,
                'major' => $this->classroom->major->name,
                'level_class' => $this->classroom->levelClass->name,
            ],
            'status' => $this->status->label(),
        ];
    }
}