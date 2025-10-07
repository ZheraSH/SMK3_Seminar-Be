<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassroomStudent extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'classroom_students';

    protected $fillable = [
        'classroom_id',
        'student_id',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'classroom_students_id');
    }
}
