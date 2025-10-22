<?php

namespace App\Traits\Models;

use App\Models\ClassroomTeachers;

trait HasManyClassroomTeachers
{
    public function classroomTeachers()
    {
        return $this->hasMany(ClassroomTeachers::class, 'classroom_id');
    }
}
