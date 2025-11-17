<?php

namespace App\Observers;

use App\Models\Rfid;
use Illuminate\Support\Str;

class RfidObserver
{
    public function creating(Rfid $rfid)
    {
        $rfid->id = Str::uuid();
    }
}
