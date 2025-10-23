<?php

namespace App\Models;

use App\Traits\Models\BelongsToClassroom;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LevelClass extends Model
{
    use HasFactory, BelongsToClassroom, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'level_classes';
    protected $fillable = [
        'name',
    ];
}
