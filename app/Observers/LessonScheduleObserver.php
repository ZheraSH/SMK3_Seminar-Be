<?php

namespace App\Observers;

use App\Models\LessonSchedule;
use Illuminate\Support\Str;

class LessonScheduleObserver
{
    public function creating(LessonSchedule $lessonSchedules)
    {
        $lessonSchedules->id = Str::uuid();
    }
}
