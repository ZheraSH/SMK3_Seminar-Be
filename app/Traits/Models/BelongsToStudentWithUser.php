<?php

namespace App\Traits\Models;

use App\Models\Student;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToStudentWithUser
{
    public function studentWithUser(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id')->with('user');
    }
}