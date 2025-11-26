<?php

namespace App\Traits\Models;

use App\Models\ClassroomStudents;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToClassroomStudents
{
    public function classroomStudent(): BelongsTo
    {
        return $this->belongsTo(ClassroomStudents::class);
    }
}