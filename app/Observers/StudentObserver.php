<?php

namespace App\Observers;

use App\Models\Student;
use Illuminate\Support\Str;

class StudentObserver
{
    public function creating(Student $student)
    {
        $student->id = Str::uuid();
    }
}
