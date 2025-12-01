<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentClassroomInfoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $student = $this->resource['student'];
        $classroom = $this->resource['classroom'];
        $classmates = $this->resource['classmates'];

        return [
            'student' => [
                'id' => $student->id,
                'name' => $student->user->name ?? null,
            ],
            'classroom' => [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'school_year' => $classroom->schoolYear->name ?? null,
                'homeroom_teacher' => $classroom->teacher ? [
                    'id' => $classroom->teacher->id,
                    'name' => $classroom->teacher->user->name ?? null,
                ] : null,
                'total_students' => $classroom->classroom_students_count ?? 0,
            ],
            'classmates' => [
                'data' => $classmates->map(function ($classroomStudent) {
                    return [
                        'id' => $classroomStudent->student->id ?? null,
                        'name' => $classroomStudent->student->user->name ?? null,
                    ];
                }),
                'pagination' => [
                    'current_page' => $classmates->currentPage(),
                    'per_page' => $classmates->perPage(),
                    'total' => $classmates->total(),
                    'last_page' => $classmates->lastPage(),
                    'from' => $classmates->firstItem(),
                    'to' => $classmates->lastItem(),
                ]
            ]
        ];
    }
}