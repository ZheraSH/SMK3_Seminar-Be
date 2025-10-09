<?php

namespace App\Models;

use App\Traits\Models\BelongsToSchool;
use App\Traits\Models\HasManyAttendanceJournal;
use App\Traits\Models\HasManyLessonSchedule;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LessonHour extends Model
{
    use HasFactory, HasManyLessonSchedule, HasManyAttendanceJournal, SoftDeletes;

    protected $guarded = ['id'];
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
