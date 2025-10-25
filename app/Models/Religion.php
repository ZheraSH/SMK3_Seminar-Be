<?php

namespace App\Models;

use App\Traits\Models\HasManyEmployees;
use App\Traits\Models\HasManyStudents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Religion extends Model
{

    use HasFactory, HasManyStudents,
    HasManyEmployees, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'religions';
    protected $fillable = [
        'name',
    ];
}
