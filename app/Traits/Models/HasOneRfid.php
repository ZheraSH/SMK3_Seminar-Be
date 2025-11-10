<?php

namespace App\Traits\Models;

use App\Models\Rfid;

trait HasOneRfid
{
    public function rfid()
    {
        return $this->hasOne(Rfid::class);
    }
}
