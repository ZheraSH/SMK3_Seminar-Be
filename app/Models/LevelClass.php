<?php

namespace App\Models;

use App\Traits\Models\BelongsToSchool;
use App\Traits\Models\HasManyClassroom;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LevelClass extends Model
{
    use HasFactory, BelongsToSchool, HasManyClassroom, SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'level_classes';
    protected $fillable = [
        'name',
        'school_id',
    ];
}
