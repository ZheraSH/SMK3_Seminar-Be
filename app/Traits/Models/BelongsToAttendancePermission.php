<?php

namespace App\Traits\Models;

use App\Models\AttendancePermission;

trait BelongsToAttendancePermission
{
    public function attendancePermission()
    {
        return $this->belongsTo(AttendancePermission::class,'overridden_by_permission_id');
    }
}