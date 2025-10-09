<?php

namespace App\Traits\Models;

use App\Models\TeacherJournal;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTeacherJournal {

    /**
     * Get the school that owns the BelongsToSchool
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function teacherJournal(): BelongsTo
    {
        return $this->belongsTo(TeacherJournal::class);
    }

}
