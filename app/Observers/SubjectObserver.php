<?php

namespace App\Observers;

use App\Models\Subject;
use Illuminate\Support\Str;

class SubjectObserver
{
    public function creating(Subject $subject)
    {
        $subject->id = Str::uuid();
    }
}
