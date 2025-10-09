<?php

namespace App\Traits\Models;

use App\Models\LevelClass;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasManyLevelClass {

    /**
     * Get all of the students for the HasManyStudent
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function levelClasses(): HasMany
    {
        return $this->hasMany(LevelClass::class);
    }

}
