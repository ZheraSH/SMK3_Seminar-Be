<?php

namespace App\Observers;

use App\Models\SchoolYear;
use Illuminate\Support\Str;
class SchoolYearObserver
{
    public function creating(SchoolYear $schoolyear)
    {
        $schoolyear->id = Str::uuid();
    }
}
