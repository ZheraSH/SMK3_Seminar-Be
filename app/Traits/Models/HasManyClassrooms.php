<?php

namespace App\Traits\Models;

use App\Models\Classroom;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasManyClassrooms {

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }
}
