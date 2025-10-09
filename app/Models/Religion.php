<?php

namespace App\Models;

use App\Traits\Models\HasManyEmployee;
use App\Traits\Models\HasManyStudent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Religion extends Model
{
    use HasFactory, HasManyStudent, HasManyEmployee, SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'religions';
    protected $fillable = [
        'name'
    ];
}
