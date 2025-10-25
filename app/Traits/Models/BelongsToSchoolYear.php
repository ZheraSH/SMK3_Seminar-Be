<?php

namespace App\Traits\Models;

use App\Models\SchoolYear;

trait BelongsToSchoolYear
{
    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }
}
