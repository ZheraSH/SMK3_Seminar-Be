<?php

namespace App\Models;

use App\Traits\Models\HasManyClassrooms;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Major extends Model
{
    use HasFactory, HasUuids, HasManyClassrooms, SoftDeletes;

    protected $table = 'majors';
    protected $fillable = [
        'name',
    ];
}
