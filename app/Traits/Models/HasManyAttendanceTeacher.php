<?php

namespace App\Traits\Models;

use App\Models\AttendanceTeacher;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasManyAttendanceTeacher {

    /**
     * Get all of the students for the HasManyStudent
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendanceTeachers(): HasMany
    {
        return $this->hasMany(AttendanceTeacher::class);
    }

}
