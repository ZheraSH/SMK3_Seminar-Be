<?php

namespace App\Traits\Models;

use App\Models\LevelClass;

trait BelongsToLevelClass
{
    public function levelClass()
    {
        return $this->belongsTo(LevelClass::class);
    }
}
