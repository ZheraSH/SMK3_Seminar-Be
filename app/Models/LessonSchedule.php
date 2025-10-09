<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Models\BelongsToClassroom;
use App\Traits\Models\BelongsToLessonHour;
use App\Traits\Models\BelongsToMapel;
use App\Traits\Models\BelongsToSchoolYear;
use App\Traits\Models\HasManyTeacherJournal;
use App\Traits\Models\BelongsToTeacherSubject;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class LessonSchedule extends Model
{
    use HasFactory, BelongsToLessonHour, BelongsToSchoolYear, BelongsToTeacherSubject, BelongsToClassroom, HasManyTeacherJournal, BelongsToMapel,SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'lesson_schedules';
    protected $fillable = [
        'classroom_id',
        'start_lesson_hours',
        'end_lesson_hours',
        'teacher_mapel_id',
        'school_year_id',
        'lesson_hour_id',
        'mapel_id',
    ];
}
