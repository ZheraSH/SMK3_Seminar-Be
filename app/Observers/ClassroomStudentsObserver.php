<?php

namespace App\Observers;

use App\Models\ClassroomStudents;
use Illuminate\Support\Str;

class ClassroomStudentsObserver
{
    public function creating(ClassroomStudents $student)
    {
        $student->id = Str::uuid();
    }
}
