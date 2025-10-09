<?php

namespace App\Traits\Models;

use App\Models\LessonSchedule;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasManyLessonSchedule {

    /**
     * Get all of the students for the HasManyStudent
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lessonScheadules(): HasMany
    {
        return $this->hasMany(LessonSchedule::class);
    }

}
