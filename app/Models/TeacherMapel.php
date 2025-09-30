<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherMapel extends Model
{
    use HasFactory;

    protected $table = 'teacher_mapels';

    protected $fillable = [
        'mapel_id',
        'employee_id',
        'school_year_id',
    ];

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id');
    }

    public function lessonSchedules()
    {
        return $this->hasMany(LessonSchedule::class, 'teacher_mapel_id');
    }
}
