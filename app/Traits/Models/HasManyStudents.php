<?php

namespace App\Traits\Models;

use App\Models\Student;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasManyStudents {

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
