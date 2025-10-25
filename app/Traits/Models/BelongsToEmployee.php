<?php

namespace App\Traits\Models;

use App\Models\Employee;

trait BelongsToEmployee
{
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
