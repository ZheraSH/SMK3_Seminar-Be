<?php

namespace App\Traits\Models;

use App\Models\User;

trait BelongsToTeacher
{
    public function teacher()
    {
        return $this->belongsTo(User::class);
    }
}
