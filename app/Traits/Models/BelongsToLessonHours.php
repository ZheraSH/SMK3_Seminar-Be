<?php

namespace App\Traits\Models;

use App\Models\LessonHour;

trait BelongsToLessonHours
{
    public function lessonHours()
    {
        return $this->belongsTo(LessonHour::class);
    }
}
