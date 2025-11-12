<?php

namespace App\Traits\Models;

use App\Models\Student;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

trait HasManyStudents
{
    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(
            Student::class,
            \App\Models\ClassroomStudents::class,
            'classroom_id',
            'id',
            'id',
            'student_id'
        )->where('classroom_students.status', \App\Enums\ClassroomStudentStatusEnum::ACTIVE->value);
    }
}