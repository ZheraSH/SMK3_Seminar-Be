<?php

namespace App\Http\Resources\Counselor\Dashboard;

use App\Enums\StudentStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CounselorHighAlphaStudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $student = $this->student;
        $classroom = null;
        if ($student && $student->classroomStudents->isNotEmpty()) {
            $activeClassroomStudent = $student->classroomStudents
                ->where('status', StudentStatusEnum::ACTIVE->value)
                ->first();
            $classroom = $activeClassroomStudent ? $activeClassroomStudent->classroom : null;
        }

        return [
            'id' => $student->id,
            'name' => $student->user->name ?? 'Unknown',
            'classroom' => $classroom ? $classroom->name : 'No Class',
            'status' => 'Alpa',
            'total_alpha' => $this->total_alpha,
        ];
    }
}
