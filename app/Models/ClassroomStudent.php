<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassroomStudent extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'classroom_student';

    protected $fillable = [
        'classroom_id',
        'id_student',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'id_student');
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'classroom_student_id');
    }
}
