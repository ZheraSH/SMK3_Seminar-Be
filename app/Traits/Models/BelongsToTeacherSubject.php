<?php

namespace App\Traits\Models;

use App\Models\TeacherSubject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTeacherSubject {

    /**
     * Get the school that owns the BelongsToSchool
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function teacherSubject(): BelongsTo
    {
        return $this->belongsTo(TeacherSubject::class);
    }

}
