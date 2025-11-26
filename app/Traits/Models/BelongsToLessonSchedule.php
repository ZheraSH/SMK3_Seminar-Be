<?php

namespace App\Traits\Models;

use App\Models\LessonSchedule;

trait BelongsToLessonSchedule
{
    public function lessonSchedule()
    {
        return $this->belongsTo(LessonSchedule::class);
    }
}
