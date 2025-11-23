<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\AttendanceStatusEnum;
use App\Enums\AttendanceProofEnum;
use App\Enums\TapTypeEnum;
use App\Traits\Models\BelongsToClassroom;
use App\Traits\Models\BelongsToRfid;
use App\Traits\Models\BelongsToStudent;

class Attendance extends Model
{
    use HasFactory, BelongsToStudent, BelongsToClassroom, BelongsToRfid, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'student_id',
        'classroom_student_id',
        'rfid_id',
        'date',
        'period',
        'checkin_time',
        'checkout_time',
        'status',
        'tap_type',
        'proof',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'checkin_time' => 'string',
        'checkout_time' => 'string',
        'status' => AttendanceStatusEnum::class,
        'tap_type' => TapTypeEnum::class,
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
            AttendanceStatusEnum::PRESENT,
            AttendanceStatusEnum::LATE
        ]);
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', AttendanceStatusEnum::ALPHA);
    }
}