<?php

namespace App\Traits\Models;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCounselor
{
    public function counselor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'counselor_id');
    }
}