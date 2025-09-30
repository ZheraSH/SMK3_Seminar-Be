<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceJournal extends Model
{
    use HasFactory;

    protected $table = 'attendance_journals';

    protected $fillable = [
        'teacher_journal_id',
        'student_classroom_id',
        'lesson_hour_id',
        'status',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function teacherJournal()
    {
        return $this->belongsTo(TeacherJournal::class, 'teacher_journal_id');
    }

    public function classroomStudent()
    {
        return $this->belongsTo(ClassroomStudent::class, 'student_classroom_id');
    }

    public function lessonHour()
    {
        return $this->belongsTo(LessonHour::class, 'lesson_hour_id');
    }
}
