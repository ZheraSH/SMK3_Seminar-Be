<?php

namespace App\Http\Resources\Counselor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CounselorHighAlphaStudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $student = $this->student;
        $classroom = null;
        if ($student && $student->classroomStudents->isNotEmpty()) {
            $classroom = $student->classroomStudents->first()->classroom;
        }

        return [
            'id' => $student->id,
            'name' => $student->user->name ?? 'Unknown',
            'classroom' => $classroom ? $classroom->name : 'No Class',
            'status' => 'Alpha',
            'total_alpha' => $this->total_alpha,
        ];
    }
}
