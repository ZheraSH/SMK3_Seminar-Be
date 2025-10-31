<?php

namespace App\Observers;

use App\Models\Semester;
use Illuminate\Support\Str;
class SemesterObserver
{
    public function creating(Semester $semester)
    {
        if (! $semester->id) {
            $semester->id = (string) Str::uuid();
        }
    }
}
