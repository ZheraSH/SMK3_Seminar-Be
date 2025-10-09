<?php

namespace App\Traits\Models;


use App\Models\Mapel;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasManyMapel {

    /**
     * Get all of the students for the HasManyStudent
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function mapel(): HasMany
    {
        return $this->hasMany(Mapel::class);
    }

}
