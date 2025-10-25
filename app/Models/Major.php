<?php

namespace App\Models;

use App\Traits\Models\HasManyClassrooms;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Major extends Model
{
    use HasFactory, HasManyClassrooms, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'majors';
    protected $fillable = [
        'name',
    ];
}
