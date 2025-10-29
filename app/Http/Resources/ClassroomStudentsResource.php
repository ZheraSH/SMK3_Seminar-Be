<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomStudentsResource extends JsonResource
{
    public function toArray($request)
    {
        $student = $this->whenLoaded('student');
        $user = $student ? $this->student->user : null;
        $classroom = $this->whenLoaded('classroom');

        return [
            'id' => $this->id,
            'classroom_id' => $this->classroom_id,
            'student_id' => $this->student_id,
            'status' => $this->status,

            'student' => $student ? [
                'id' => $this->student->id,
                'name' => $user->name ?? 'Nama tidak tersedia',
                'nisn' => $this->student->nisn,
                'gender' => $this->student->gender?->label() ?? 'Tidak diketahui',
                'religion' => $this->student->religion?->name,
                'birth_place' => $this->student->birth_place,
                'birth_date' => $this->student->birth_date,
                'number_akta' => $this->student->number_akta,
                'order_child' => $this->student->order_child,
                'count_siblings' => $this->student->count_siblings,
                'address' => $this->student->address,
            ] : null,

            'classroom' => $classroom ? [
                'id' => $this->classroom->id,
                'name' => $this->classroom->name,
            ] : null,
        ];
    }
}