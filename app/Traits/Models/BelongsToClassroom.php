<?php

namespace App\Traits\Models;

use App\Models\Classroom;

trait BelongsToClassroom
{
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}
