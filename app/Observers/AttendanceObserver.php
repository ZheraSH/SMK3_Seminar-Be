<?php

namespace App\Observers;

use App\Models\Attendance;
use Illuminate\Support\Str;

class AttendanceObserver
{
    public function creating(Attendance $attendance)
    {
        $attendance->attendance = Str::uuid();
    }
}
