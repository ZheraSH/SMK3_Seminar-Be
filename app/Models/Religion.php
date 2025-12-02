<?php

namespace App\Models;

use App\Traits\Models\HasManyEmployees;
use App\Traits\Models\HasManyStudents;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Religion extends Model
{

    use HasFactory, HasUuids, HasManyStudents,
    HasManyEmployees, SoftDeletes;

    protected $table = 'religions';
    protected $fillable = [
        'name',
    ];
}
