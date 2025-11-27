<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\AttendanceStatusEnum;
use App\Enums\AttendanceProofEnum;
use App\Traits\Models\BelongsToClassroomStudents;
use App\Traits\Models\BelongsToRfid;
use App\Traits\Models\BelongsToStudent;
use App\Traits\Models\BelongsToSubject;
use App\Traits\Models\BelongsToTeacher;
use App\Traits\Models\BelongsToLessonSchedule;

class Attendance extends Model
{
    use HasFactory, BelongsToStudent, 
    BelongsToSubject, BelongsToTeacher,
    BelongsToLessonSchedule, BelongsToClassroomStudents,
    BelongsToRfid, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'classroom_student_id',
        'student_id',
        'lesson_schedule_id',
        'teacher_id',
        'subject_id',
        'rfid_id',
        'date',
        'checkin_time',
        'checkout_time',
        'lesson_order',
        'attendance_type',
        'status',
        'proof',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'checkin_time' => 'string',
        'checkout_time' => 'string',
        'status' => AttendanceStatusEnum::class,
        'proof' => AttendanceProofEnum::class,
    ];

    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
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
            AttendanceStatusEnum::PRESENT->value,
            AttendanceStatusEnum::LATE->value
        ]);
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', AttendanceStatusEnum::ALPHA->value);
    }
}