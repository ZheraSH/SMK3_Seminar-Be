<?php

namespace App\Traits\Models;

use App\Models\Attendance;
use App\Models\AttendanceRfid;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait StudentHasManyAttendances
{
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function attendanceRfids(): HasMany
    {
        return $this->hasMany(AttendanceRfid::class, 'student_id');
    }
}
