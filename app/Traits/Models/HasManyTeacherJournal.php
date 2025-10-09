<?php

namespace App\Traits\Models;

use App\Models\TeacherJournal;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasManyTeacherJournal {

    /**
     * Get all of the students for the HasManyStudent
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function teacherJournals(): HasMany
    {
        return $this->hasMany(TeacherJournal::class);
    }

}
