<?php

namespace App\Traits\Models;

use App\Models\LessonHour;
use App\Models\LevelClass;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToLevelClass {
    /**
     * Get the user that owns the HasUser
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function levelClass(): BelongsTo
    {
        return $this->belongsTo(LevelClass::class);
    }
}
