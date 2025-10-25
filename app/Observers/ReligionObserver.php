<?php

namespace App\Observers;

use App\Models\Religion;
use Illuminate\Support\Str;

class ReligionObserver
{
    public function creating(Religion $religion)
    {
        if (! $religion->id) {
            $religion->id = (string) Str::uuid();
        }
    }
}
