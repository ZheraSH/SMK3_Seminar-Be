<?php

namespace App\Traits\Models;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasManyAttendance {

    /**
     * Get all of the students for the HasManyStudent
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

}
