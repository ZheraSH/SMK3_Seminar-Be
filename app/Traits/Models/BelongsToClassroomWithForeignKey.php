<?php

namespace App\Traits\Models;

use App\Models\Classroom;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToClassroomWithForeignKey
{
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }
}