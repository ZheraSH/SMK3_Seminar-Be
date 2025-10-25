<?php

namespace App\Traits\Models;

use App\Models\Subject;

trait BelongsToSubject
{
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
