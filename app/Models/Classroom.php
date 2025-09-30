<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $table = 'classrooms';

    protected $fillable = [
        'level_class_id',
        'school_year_id',
        'name',
        'teacher_id',
        'slug',
        'major_id',
    ];

    public function levelClass()
    {
        return $this->belongsTo(LevelClass::class, 'level_class_id');
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Employee::class, 'teacher_id');
    }

    public function major()
    {
        return $this->belongsTo(Major::class, 'major_id');
    }

    public function classroomStudents()
    {
        return $this->hasMany(ClassroomStudent::class, 'classroom_id');
    }

    public function lessonSchedules()
    {
        return $this->hasMany(LessonSchedule::class, 'classroom_id');
    }
}
