<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'major' => $this->major?->name,
            'level_class' => $this->levelclass?->name,
            'school_year' => $this->schoolyear?->name,
            'teacher' => $this->whenLoaded('teacher', function() {
                return $this->teacher?->user->only(['id','name','email']);
            }),

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