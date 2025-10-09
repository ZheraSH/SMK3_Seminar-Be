<?php

namespace App\Traits\Models;

use App\Models\School;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToSchool {

    /**
     * Get the school that owns the BelongsToSchool
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

}
