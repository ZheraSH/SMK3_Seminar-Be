<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendance';

    protected $fillable = [
        'classroom_student_id',
        'point',
        'status',
        'proof',
    ];

    public function classroomStudent()
    {
        return $this->belongsTo(ClassroomStudent::class, 'classroom_student_id');
    }
}
