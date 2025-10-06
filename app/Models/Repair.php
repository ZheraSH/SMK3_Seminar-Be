<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Repair extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'repairs';

    protected $fillable = [
        'name',
        'point',
        'start_date',
        'end_date',
        'is_approved',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_approved' => 'boolean',
    ];

    public function studentRepairs()
    {
        return $this->hasMany(StudentRepair::class, 'Repair_id');
    }
}
