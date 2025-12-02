<?php

namespace App\Models;

use App\Enums\DayEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Models\BelongsToClassroom;
use App\Traits\Models\BelongsToEmployee;
use App\Traits\Models\BelongsToLessonHour;
use App\Traits\Models\BelongsToSchoolYear;
use App\Traits\Models\BelongsToSubject;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LessonSchedule extends Model
{
    use HasFactory, HasUuids, BelongsToClassroom,
    BelongsToEmployee, BelongsToSchoolYear,
    BelongsToLessonHour, BelongsToSubject,
    SoftDeletes;

    protected $table = 'lesson_schedules';
    protected $fillable = [
        'day',
        'classroom_id',
        'lesson_hour_id',
        'employee_id',
        'subject_id',
    ];

    protected $casts = [
        'day' => DayEnum::class,
    ];
}