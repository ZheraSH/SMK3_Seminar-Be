<?php

namespace App\Observers;

use App\Models\LessonHour;
use Illuminate\Support\Str;

class LessonHourObserver
{
    public function creating(LessonHour $lessonHour)
    {
        $lessonHour->id = Str::uuid();
    }
}
