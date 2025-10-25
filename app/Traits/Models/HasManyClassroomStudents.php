<?php

namespace App\Traits\Models;

use App\Models\ClassroomStudents;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasManyClassroomStudents {

    public function classroomStudents(): HasMany
    {
        return $this->hasMany(ClassroomStudents::class);
    }
}
