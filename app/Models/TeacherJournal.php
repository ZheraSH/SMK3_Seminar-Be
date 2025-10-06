<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherJournal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'teacher_journals';

    protected $fillable = [
        'lesson_schedule_id',
        'description',
        'photo',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function lessonSchedule()
    {
        return $this->belongsTo(LessonSchedule::class, 'lesson_schedule_id');
    }
}
