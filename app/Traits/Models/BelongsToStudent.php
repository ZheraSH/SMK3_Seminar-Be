<?php

namespace App\Traits\Models;

use App\Models\Student;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToStudent
{
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}