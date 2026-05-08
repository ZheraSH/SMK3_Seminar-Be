<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\AttendanceStatusEnum;
use App\Traits\Models\BelongsToAttendancePermission;
use App\Traits\Models\BelongsToClassroomStudents;
use App\Traits\Models\BelongsToStudent;
use App\Traits\Models\BelongsToSubject;
use App\Traits\Models\BelongsToTeacher;
use App\Traits\Models\BelongsToLessonSchedule;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Attendance extends Model
{
    use HasFactory, HasUuids, BelongsToStudent,
    BelongsToSubject, BelongsToTeacher,
    BelongsToLessonSchedule, BelongsToClassroomStudents,
    BelongsToAttendancePermission, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    protected $table = 'attendances';
    protected $fillable = [
        'classroom_student_id',
        'student_id',
        'lesson_schedule_id',
        'teacher_id',
        'subject_id',
        'date',
        'lesson_order',
        'status',
        'is_final',
        'is_locked',
        'overridden_by_permission_id',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'status' => AttendanceStatusEnum::class,
        'is_final' => 'boolean',
        'is_locked' => 'boolean',
    ];

    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    public function scopeFinal($query)
    {
        return $query->where('is_final', true);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeByClassroom($query, $classroomId)
    {
        return $query->whereHas('classroomStudent', function ($q) use ($classroomId) {
            $q->where('classroom_id', $classroomId);
        });
    }

    public function scopePresent($query)
    {
        return $query->whereIn('status', [
            AttendanceStatusEnum::PRESENT->value
        ]);
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', AttendanceStatusEnum::ALPHA->value);
    }
}
