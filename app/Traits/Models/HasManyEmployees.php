<?php

namespace App\Traits\Models;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasManyEmployees {

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
