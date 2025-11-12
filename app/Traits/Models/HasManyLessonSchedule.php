<?php

namespace App\Traits\Models;

use App\Models\LessonSchedule;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasManyLessonSchedule
{
    public function lessonSchedules(): HasMany
    {
        return $this->hasMany(LessonSchedule::class);
    }
}