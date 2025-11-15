<?php

namespace App\Traits\Models;

use App\Models\Student;
use App\Models\ClassroomStudents;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

trait HasManyStudents
{
    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(
            Student::class,
            ClassroomStudents::class,
            'classroom_id',
            'id',
            'id',
            'student_id'
        )
        ->where(function ($q) {
            $q->where('classroom_students.status', \App\Enums\StudentStatusEnum::ACTIVE->value);
        });
    }
}