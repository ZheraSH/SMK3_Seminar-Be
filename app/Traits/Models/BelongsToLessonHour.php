<?php

namespace App\Traits\Models;

use App\Models\LessonHour;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToLessonHour {
    /**
     * Get the user that owns the HasUser
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function start(): BelongsTo
    {
        return $this->belongsTo(LessonHour::class,'lesson_hour_start');
    }

        /**
     * Get the user that owns the HasUser
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function end(): BelongsTo
    {
        return $this->belongsTo(LessonHour::class, 'lesson_hour_end');
    }

    public function lessonHour(): BelongsTo
    {
        return $this->belongsTo(LessonHour::class);
    }
}
