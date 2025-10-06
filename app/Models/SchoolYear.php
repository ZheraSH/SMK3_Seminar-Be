<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolYear extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'school_years';

    protected $fillable = [
        'school_year',
        'active',
        'school_id',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function lessonSchedules()
    {
        return $this->hasMany(LessonSchedule::class, 'school_year_id');
    }

    public function teacherMapels()
    {
        return $this->hasMany(TeacherMapel::class, 'school_year_id');
    }
}
