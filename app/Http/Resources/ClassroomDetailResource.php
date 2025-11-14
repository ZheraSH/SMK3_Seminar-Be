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
                    'email' => $this->teacher->user->email
                ] : null;
            }),
            'statistics' => [
                'total_students' => $this->whenLoaded('classroomStudents', function() {
                    return $this->classroomStudents->where('status', \App\Enums\StudentStatusEnum::ACTIVE)->count();
                }, 0),
                'total_schedules' => $this->whenLoaded('lessonSchedules', function() {
                    return $this->lessonSchedules->count();
                }, 0),
                'total_subjects' => $this->whenLoaded('lessonSchedules', function() {
                    return $this->lessonSchedules->unique('subject_id')->count();
                }, 0),
            ],
            'students' => $this->whenLoaded('classroomStudents', function() {
                return $this->classroomStudents
                    ->where('status', \App\Enums\StudentStatusEnum::ACTIVE)
                    ->map(function($classroomStudent) {
                        return [
                            'id' => $classroomStudent->student->id,
                            'name' => $classroomStudent->student->user->name,
                            'nisn' => $classroomStudent->student->nisn,
                            'gender' => $classroomStudent->student->gender?->label(),
                            'status' => $classroomStudent->status?->label(),
                        ];
                    })->values();
            }, []),
            'schedules_overview' => $this->whenLoaded('lessonSchedules', function() {
                return $this->lessonSchedules->groupBy('day')->map(function($schedules, $day) {
                    return [
                        'day' => $day,
                        'day_label' => \App\Enums\DayEnum::tryFrom($day)?->label(),
                        'total_lessons' => $schedules->count(),
                    ];
                })->values();
            }, []),
        ];
    }
}