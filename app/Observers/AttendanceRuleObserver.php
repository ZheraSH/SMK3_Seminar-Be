<?php

namespace App\Observers;

use App\Models\AttendanceRule;
use Illuminate\Support\Str;

class AttendanceRuleObserver
{
    public function creating(AttendanceRule $attendanceRule)
    {
        $attendanceRule->id = Str::uuid();
    }
}
