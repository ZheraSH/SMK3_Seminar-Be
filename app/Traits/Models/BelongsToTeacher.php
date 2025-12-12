<?php

namespace App\Traits\Models;

use App\Models\Employee;

trait BelongsToTeacher
{
    public function teacher()
    {
        // For Classroom model, use homeroom_teacher_id
        // For other models like LessonSchedule, use teacher_id
        $foreignKey = property_exists($this, 'homeroom_teacher_id') || in_array('homeroom_teacher_id', $this->fillable ?? []) 
            ? 'homeroom_teacher_id' 
            : 'teacher_id';
        
        return $this->belongsTo(Employee::class, $foreignKey)->with('user');
    }

    public function employee()
    {
        return $this->teacher();
    }
}