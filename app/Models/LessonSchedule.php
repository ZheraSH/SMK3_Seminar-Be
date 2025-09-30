<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonSchedule extends Model
{
    use HasFactory;

    protected $table = 'lesson_schedules';

    protected $fillable = [
        'classroom_id',
        'start_lesson_hours',
        'end_lesson_hours',
        'teacher_mapel_id',
        'school_year_id',
        'lesson_hour_id',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function teacherMapel()
    {
        return $this->belongsTo(TeacherMapel::class, 'teacher_mapel_id');
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id');
    }

    public function lessonHour()
    {
        return $this->belongsTo(LessonHour::class, 'lesson_hour_id');
    }
}
