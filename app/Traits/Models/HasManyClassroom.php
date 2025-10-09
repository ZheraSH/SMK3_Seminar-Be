<?php

namespace App\Traits\Models;

use App\Models\Classroom;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasManyClassroom {

    /**
     * Get all of the students for the HasManyStudent
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

}
