<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'major' => $this->whenLoaded('major', fn() => $this->major->only(['id', 'name'])),
            'level_class' => $this->whenLoaded('levelClass', fn() => $this->levelClass->only(['id', 'name'])),
            'school_year' => $this->whenLoaded('schoolYear', fn() => $this->schoolYear->only(['id', 'name'])),
            'homeroom_teacher' => $this->whenLoaded('teacher', function() {
                return $this->teacher?->user ? [
                    'id' => $this->teacher->id,
                    'name' => $this->teacher->user->name,
                ] : null;
            }),
            'students' => $this->whenLoaded('classroomStudents', function() {
                return $this->classroomStudents
                ->where('status', \App\Enums\StudentStatusEnum::ACTIVE)
                ->map(function($classroomStudent) {
                        $student = $classroomStudent->student;
                        
                        return [
                            'id' => $student->id,
                            'name' => $student->user->name,
                            'nisn' => $student->nisn,
                            'gender' => $student->gender?->label(),
                            'status' => $classroomStudent->status?->label(),
                        
                            'rfid' => $student->rfid ? [
                                'id' => $student->rfid->id,
                                'rfid' => $student->rfid->rfid,
                            ] : null,
                        ];
                    })->values();
            }, []),
            'statistics' => [
                'total_students' => $this->whenLoaded('classroomStudents', function() {
                    return $this->classroomStudents->where('status', \App\Enums\StudentStatusEnum::ACTIVE)->count();
                }, 0),
            ],
        ];
    }
}