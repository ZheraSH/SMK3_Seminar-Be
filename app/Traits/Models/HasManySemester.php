<?php

namespace App\Traits\Models;

use App\Models\School;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasManySemester {

    /**
     * Get the school that owns the BelongsToSchool
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function semester(): HasMany
    {
        return $this->hasMany(Semester::class);
    }

}
