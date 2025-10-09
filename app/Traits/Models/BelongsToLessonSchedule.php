<?php

namespace App\Traits\Models;

use App\Models\LessonSchedule;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToLessonSchedule {
    /**
     * Get the user that owns the HasUser
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lessonSchedule(): BelongsTo
    {
        return $this->belongsTo(LessonSchedule::class);
    }
}
