<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomStudentsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'classroom_id' => $this->classroom_id,
            'student_id' => $this->student_id,
            'status' => $this->status,
            // 'rfid' => $this->rfid,
            'students' => $this->whenLoaded('classroomStudents', function() {
                return $this->classroomStudents->map(function($classroomStudent) {
                    if (!$classroomStudent->student || !$classroomStudent->student->user) {
                        return null;
                    }

                    return [
                        'id' => $classroomStudent->student->id,
                        'name' => $classroomStudent->student->user->name,
                        'nisn' => $classroomStudent->student->nisn,
                        'current_class' => $this->name,
                        'gender' => $classroomStudent->student->gender?->label() ?? 'Tidak diketahui',
                        'religion' => $classroomStudent->student->religion?->name,
                        'birth_place' => $classroomStudent->student->birth_place,
                        'birth_date' => $classroomStudent->student->birth_date,
                        'number_akta' => $classroomStudent->student->number_akta,
                        'order_child' => $classroomStudent->student->order_child,
                        'count_siblings' => $classroomStudent->student->count_siblings,
                        'address' => $classroomStudent->student->address,
                        'status' => $classroomStudent->status?->label() ?? 'Tidak diketahui', 
                        'pivot_id' => $classroomStudent->id,
                    ];
                })->filter();
            }),
        ];
    }
    
}
