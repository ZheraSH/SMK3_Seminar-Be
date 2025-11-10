<?php

namespace App\Traits\Models;

use App\Models\Rfid;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasManyRfids
{
    public function rfids(): HasMany
    {
        return $this->hasMany(Rfid::class, 'student_id');
    }
}
