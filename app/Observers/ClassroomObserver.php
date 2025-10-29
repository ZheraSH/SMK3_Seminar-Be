<?php

namespace App\Observers;

use App\Models\Classroom;
use Illuminate\Support\Str;

class ClassroomObserver
{
    public function creating(Classroom $classroom)
    {
        $classroom->id = Str::uuid();
    }
}
