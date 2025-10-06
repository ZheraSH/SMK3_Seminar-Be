<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePosition extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employee_position';

    protected $fillable = [
        'employee_id',
        'position_id',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }
}
