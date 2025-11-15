<?php

namespace App\Traits\Models;

use App\Models\ClassroomStudents;

trait BelongsToClassroomStudents
{
    public function classroomStudent()
    {
        return $this->belongsTo(ClassroomStudents::class);
    }
}
