<?php

namespace App\Models;

use App\Traits\Models\HasManyClassroom;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Major extends Model
{
    use HasFactory, HasManyClassroom, SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'majors';
    protected $fillable = [
        'name'
    ];

}
