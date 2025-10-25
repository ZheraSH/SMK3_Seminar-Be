<?php

namespace App\Traits\Models;

use App\Models\Major;

trait BelongsToMajor
{
    public function major()
    {
        return $this->belongsTo(Major::class);
    }
}
