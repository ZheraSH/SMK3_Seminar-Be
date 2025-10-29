<?php

namespace App\Models;

use App\Traits\Models\BelongsToClassroomWithForeignKey;
use App\Traits\Models\BelongsToStudentWithForeignKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassroomStudents extends Model
{
    use HasFactory, 
        BelongsToClassroomWithForeignKey,
        BelongsToStudentWithForeignKey,
        SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'classroom_students';
    protected $fillable = [
        'classroom_id',
        'student_id',
        'status'
    ];
    
    protected $casts = [
        'status' => \App\Enums\ClassroomStudentStatusEnum::class,
    ];
}