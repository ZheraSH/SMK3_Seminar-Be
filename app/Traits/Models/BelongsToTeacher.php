<?php

namespace App\Traits\Models;

use App\Models\Employee;

trait BelongsToTeacher
{
    public function teacher()
    {
        return $this->belongsTo(Employee::class, 'teacher_id')->with('user');
    }

    public function employee()
    {
        return $this->teacher();
    }
}