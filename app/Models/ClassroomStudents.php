<?php

namespace App\Models;

use App\Traits\Models\BelongsToStudent;
use App\Traits\Models\BelongsToClassroom;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ClassroomStudents extends Model
{
    use HasFactory, HasUuids, BelongsToClassroom,
        BelongsToStudent, SoftDeletes;

    protected $table = 'classroom_students';
    protected $fillable = [
        'classroom_id',
        'student_id',
        'status'
    ];
    
    protected $casts = [
        'status' => \App\Enums\StudentStatusEnum::class,
    ];
}