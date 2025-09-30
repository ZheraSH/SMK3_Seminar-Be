<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelClass extends Model
{
    use HasFactory;

    protected $table = 'level_classes';

    protected $fillable = [
        'name'
    ];

    public function classrooms()
    {
        return $this->hasMany(Classroom::class, 'level_class_id');
    }
}
