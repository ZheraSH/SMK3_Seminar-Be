<?php

namespace App\Traits\Models;

use App\Models\Religion;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToReligion {
    /**
     * Get the user that owns the HasUser
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function religion(): BelongsTo
    {
        return $this->belongsTo(Religion::class);
    }
}
