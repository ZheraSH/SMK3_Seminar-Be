<?php

namespace App\Traits\Models;

use App\Models\Student;

trait HasOneStudent
{
    public function student()
    {
        return $this->hasOne(Student::class, 'user_id');
    }
}
