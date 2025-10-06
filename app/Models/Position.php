<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'position';

    protected $fillable = [
        'name',
        'description'
    ];

    public function employeePositions()
    {
        return $this->hasMany(EmployeePosition::class, 'position_id');
    }
}
