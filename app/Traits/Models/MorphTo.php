<?php

namespace App\Traits\Models;

use App\Models\ClassroomStudent;

trait MorphTo {

    public function model()
    {
        return $this->morphTo();
    }
}
