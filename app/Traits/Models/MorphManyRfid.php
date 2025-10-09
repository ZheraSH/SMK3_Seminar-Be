<?php

namespace App\Traits\Models;

use App\Models\ModelHasRfid;

trait MorphManyRfid {

    public function modelHasRfid()
    {
        return $this->morphOne(ModelHasRfid::class, 'model');
    }
}
