<?php

namespace App\Observers;

use App\Models\LevelClass;
use Illuminate\Support\Str;

class LevelClassObserver
{
    public function creating(LevelClass $class)
    {
        $class->id = Str::uuid();
    }
}
