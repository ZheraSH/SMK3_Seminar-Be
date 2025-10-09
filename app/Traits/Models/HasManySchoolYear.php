<?php

namespace App\Traits\Models;

use App\Models\SchoolYear;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasManySchoolYear {

    /**
     * Get all of the students for the HasManyStudent
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function schoolYears(): HasMany
    {
        return $this->hasMany(SchoolYear::class);
    }

}
