<?php

namespace App\Traits\Models;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToEmployee {
    /**
     * Get the user that owns the HasUser
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
