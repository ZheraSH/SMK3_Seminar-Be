<?php

namespace App\Models;

use App\Traits\Models\BelongsToClassroom;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LevelClass extends Model
{
    use HasFactory, HasUuids, BelongsToClassroom, SoftDeletes;

    protected $table = 'level_classes';
    protected $fillable = [
        'name',
    ];
}
