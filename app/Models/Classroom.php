<?php

namespace App\Models;

use App\Traits\Models\HasManyLessonSchedule;
use App\Traits\Models\BelongsToLevelClass;
use App\Traits\Models\BelongsToMajor;
use App\Traits\Models\BelongsToSchoolYear;
use App\Traits\Models\BelongsToTeacher;
use App\Traits\Models\HasManyClassroomStudents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Classroom extends Model
{
    use HasFactory, HasUuids, BelongsToMajor,
        BelongsToLevelClass, BelongsToSchoolYear,
        BelongsToTeacher, HasManyClassroomStudents,
        HasManyLessonSchedule, SoftDeletes;

    protected $table = 'classrooms';
    protected $fillable = [
        'name',
        'major_id',
        'slug',
        'level_class_id',
        'school_year_id',
        'teacher_id',
    ];
}