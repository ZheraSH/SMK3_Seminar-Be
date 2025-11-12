<?php

namespace App\Traits\Models;

use App\Models\LessonHour;

trait BelongsToLessonHour
{
    public function lessonHour()
    {
        return $this->belongsTo(LessonHour::class);
    }
}
