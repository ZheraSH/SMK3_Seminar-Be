<?php

namespace App\Traits\Models;

use App\Models\Student;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToStudentWithForeignKey
{
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}