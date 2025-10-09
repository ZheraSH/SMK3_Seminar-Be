<?php

namespace App\Models;

use App\Traits\Models\BelongsToClassroom;
use App\Traits\Models\BelongsToStudent;
use App\Traits\Models\HasManyAttendanceJournal;
use App\Traits\Models\HasManyStudentRepair;
use App\Traits\Models\HasManyStudentViolation;
use App\Traits\Models\MorphManyAttendance;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassroomStudent extends Model
{
    use HasFactory, BelongsToClassroom, BelongsToStudent, MorphManyAttendance, HasManyAttendanceJournal, SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'classroom_students';
    protected $fillable = [
        'classroom_id',
        'student_id',
    ];
}
