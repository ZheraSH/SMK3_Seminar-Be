<?php

namespace App\Observers;

use App\Models\Major;
use Illuminate\Support\Str;

class MajorObserver
{
    public function creating( Major $major)
    {
        $major->id = Str::uuid();
    }
}
