<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonHour extends Model
{
    use HasFactory;

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
