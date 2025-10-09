<?php

namespace App\Traits\Models;

use App\Models\TeacherMapel;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasManyTeacherMapel {
    public function teacherMapel(): HasMany
    {
        return $this->hasMany(TeacherMapel::class);
    }

}
