<?php

namespace App\Traits\Models;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait StudentHasManyAttendances
{
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }
}
