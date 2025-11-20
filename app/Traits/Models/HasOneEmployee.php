<?php

namespace App\Traits\Models;

use App\Models\Employee;

trait HasOneEmployee
{
    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id');
    }
}
