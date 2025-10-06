<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LessonHour extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lesson_hours';

    protected $fillable = [
        'name',
        'start',
        'end'
    ];

    protected $casts = [
        'start' => 'string',
        'end' => 'string',
    ];

    public function lessonSchedules()
    {
        return $this->hasMany(LessonSchedule::class, 'lesson_hour_id');
    }
}
