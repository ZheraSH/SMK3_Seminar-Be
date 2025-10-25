<?php

namespace App\Traits\Models;

use App\Models\Religion;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToReligion {

    public function religion(): BelongsTo
    {
        return $this->belongsTo(Religion::class);
    }
}
