<?php

namespace App\Traits\Models;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait BelongsToManySubjects
{
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(
            Subject::class,
            'lesson_schedules',
            'teacher_id',
            'subject_id'
        )->distinct();
    }
}
