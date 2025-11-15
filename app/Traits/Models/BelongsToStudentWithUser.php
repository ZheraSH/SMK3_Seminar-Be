<?php

namespace App\Traits\Models;

use App\Models\Student;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToStudentWithUser
{
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id')
            ->with([
                'user',
                'classroomStudents.classroom'
            ]);
    }
}
