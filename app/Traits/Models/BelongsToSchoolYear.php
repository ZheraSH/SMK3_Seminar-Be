<?php

namespace App\Traits\Models;

use App\Models\SchoolYear;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToSchoolYear {

    /**
     * Get the school that owns the BelongsToSchool
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

}
