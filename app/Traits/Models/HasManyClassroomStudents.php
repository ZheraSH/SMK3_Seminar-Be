<?php
namespace App\Traits\Models;

use App\Models\ClassroomStudents;
use App\Models\Student;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasManyClassroomStudents {
    public function classroomStudents(): HasMany
    {
        return $this->hasMany(ClassroomStudents::class, 'classroom_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'classroom_students', 'classroom_id', 'student_id')
            ->withPivot('status')
            ->withTimestamps();
    }
}