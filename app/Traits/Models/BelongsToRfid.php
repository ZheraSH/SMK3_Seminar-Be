<?php

namespace App\Traits\Models;

use App\Models\Rfid;

trait BelongsToRfid
{
    public function rfid()
    {
        return $this->belongsTo(Rfid::class);
    }
}
