<?php

namespace App\Traits\Models;

use App\Models\AttendanceJournal;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasManyAttendanceJournal {

    /**
     * Get all of the students for the HasManyStudent
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendanceJournals(): HasMany
    {
        return $this->hasMany(AttendanceJournal::class);
    }

}
