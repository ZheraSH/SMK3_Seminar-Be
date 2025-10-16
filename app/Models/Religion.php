<?php

namespace App\Models;

use App\Traits\Models\HasManyEmployee;
use App\Traits\Models\HasManyStudent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Religion extends Model
{
    /** @use HasFactory<\Database\Factories\ReligionFactory> */
    use HasFactory,
    HasManyStudent,
    HasManyEmployee,
    SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'religions';
    protected $fillable = [
        'name',
    ];
}
