<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\ClassroomStudentStatusEnum;
use App\Enums\GenderEnum;

class ClassroomDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $classroomStudents = $this->whenLoaded('classroomStudents') ?? collect();

        $activeStudents = $classroomStudents->where('status', ClassroomStudentStatusEnum::ACTIVE);
        $totalActiveStudents = $activeStudents->count();

        $lessonSchedules = $this->whenLoaded('lessonSchedules') ?? collect();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,

            'class_info' => [
                'major' => $this->major?->name ?? '-',
                'level_class' => $this->levelClass?->name ?? '-',
                'school_year' => $this->schoolYear?->name ?? '-',
            ],

            'homeroom_teacher' => $this->whenLoaded('teacher', function () {
                $teacher = $this->teacher?->user;
                return $teacher ? [
                    'id' => $this->teacher->id,
                    'name' => $teacher->name,
                    'email' => $teacher->email,
                ] : null;
            }),

            'statistics' => [
                'total_students' => $totalActiveStudents,
                'total_male_students' => $activeStudents->filter(fn($s) => $s->student?->gender === GenderEnum::MALE)->count(),
                'total_female_students' => $activeStudents->filter(fn($s) => $s->student?->gender === GenderEnum::FEMALE)->count(),
                'total_schedules' => $lessonSchedules->count(),
                'total_subjects' => $lessonSchedules->unique('subject_id')->count(),
            ],

            'students' => $activeStudents->map(function ($classroomStudent) {
                $student = $classroomStudent->student;
                $user = $student?->user;

                if (!$student || !$user) {
                    return null;
                }

                return [
                    'id' => $student->id,
                    'name' => $user->name,
                    'nisn' => $student->nisn,
                    'current_class' => $this->name,
                    'gender' => $student->gender?->label() ?? 'Tidak diketahui',
                    'religion' => $student->religion?->name ?? '-',
                    'birth_place' => $student->birth_place ?? '-',
                    'birth_date' => $student->birth_date?->format('d-m-Y'),
                    'number_akta' => $student->number_akta ?? '-',
                    'order_child' => $student->order_child ?? 0,
                    'count_siblings' => $student->count_siblings ?? 0,
                    'address' => $student->address ?? '-',
                    'status' => $classroomStudent->status?->label() ?? 'Tidak diketahui',
                    'pivot_id' => $classroomStudent->id,
                    'enrollment_date' => $classroomStudent->created_at?->format('d-m-Y'),
                ];
            })->filter()->values(),
        ];
    }
}
