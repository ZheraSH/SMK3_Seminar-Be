<?php

namespace App\Traits\Models;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasManyAttendances
{
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'classroom_student_id');
    }
}