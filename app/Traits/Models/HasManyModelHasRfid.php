<?php

namespace App\Traits\Models;

use App\Models\ModelHasRfid;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasManyModelHasRfid {

    /**
     * Get all of the students for the HasManyStudent
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function modelHasRfids(): HasMany
    {
        return $this->hasMany(ModelHasRfid::class);
    }

}
