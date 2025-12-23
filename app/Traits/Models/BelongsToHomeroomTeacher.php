<?php

namespace App\Traits\Models;

use App\Models\Employee;

trait BelongsToHomeroomTeacher
{
    public function homeroomTeacher()
    {
        return $this->belongsTo(Employee::class, 'homeroom_teacher_id')->with('user');
    }
}