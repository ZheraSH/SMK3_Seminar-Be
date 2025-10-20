<?php

namespace App\Traits\Models;

use App\Models\Attendance;

trait MorphManyAttendance {

    public function attendances()
    {
        return $this->morphMany(Attendance::class, 'model');
    }
}
