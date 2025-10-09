<?php

namespace App\Models;

use App\Enums\AttendanceEnum;
use App\Traits\Models\BelongsToClassroomStudent;
use App\Traits\Models\BelongsToLessonHour;
use App\Traits\Models\BelongsToTeacherJournal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceJournal extends Model
{
    use HasFactory, BelongsToTeacherJournal, BelongsToClassroomStudent, BelongsToLessonHour, SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'attendanceJournal';
    protected $casts = [
        'status' => AttendanceEnum::class,
    ];
}
