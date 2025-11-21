<?php

namespace App\Observers;

use App\Models\AttendancePermission;
use Illuminate\Support\Str;

class AttendancePermissionObserver
{
    public function creating(AttendancePermission $attendancePermission): void
    {
        $attendancePermission->id = Str::uuid();
    }
}