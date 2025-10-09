<?php

namespace App\Models;

use App\Traits\Models\BelongsToLessonSchedule;
use App\Traits\Models\HasManyAttendanceJournal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherJournal extends Model
{
    use HasFactory, BelongsToLessonSchedule, HasManyAttendanceJournal, SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'teacher_journals';
    protected $fillable = [
        'lesson_schedule_id',
        'title',
        'description',
        'photo',
        'date',
    ];
    protected $casts = [
        'date' => 'date',
    ];
}
