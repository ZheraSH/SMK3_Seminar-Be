<?php

namespace App\Traits\Models;

use App\Models\ClassroomStudent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToClassroomStudent {
    /**
     * Get the user that owns the HasUser
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function classroomStudent(): BelongsTo
    {
        return $this->belongsTo(ClassroomStudent::class);
    }
}
