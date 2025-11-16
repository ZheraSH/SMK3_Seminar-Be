<?php
namespace App\Traits\Models;

use App\Models\ClassroomStudents;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait StudentHasManyClassroomStudents
{
    public function classroomStudents(): HasMany
    {
        return $this->hasMany(ClassroomStudents::class, 'student_id');
    }
}