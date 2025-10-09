<?php

namespace App\Traits\Models;

use App\Models\Mapel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToMapel {
    public function Mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

}
