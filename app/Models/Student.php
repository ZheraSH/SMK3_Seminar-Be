<?php

namespace App\Models;

use illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $table = 'student';

    protected $fillable = [
        'user_id',
        'nisn',
        'religion_id',
        'birthplace',
        'birthdate',
        'address',
        'nik',
        'no_kk',
        'no_birth_certificate',
        'order_child',
        'count_siblings',
        'point',
    ];

    protected $casts = [
        'birthdate' => 'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function religion()
    {
        return $this->belongsTo(Religion::class,'religion_id');
    }

    public function classroomStudent()
    {
        return $this->hasMany(ClassroomStudent::class,'id_student');
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class,'classroom_student_id');
    }

    public function studentViolation()
    {
        return $this->hasMany(StudentViolation::class,'Student_id');
    }

    public function studentRepairs()
    {
        return $this->hasMany(StudentRepair::class,'Student_id');
    }
}
