<?php

namespace App\Models;

use App\Enums\AttendanceStatusEnum;
use App\Traits\Models\BelongsToClassroomStudents;
use App\Traits\Models\BelongsToRfid;
use App\Traits\Models\BelongsToStudent;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceRfid extends Model
{
    use HasFactory, HasUuids, BelongsToStudent, BelongsToClassroomStudents, BelongsToRfid, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    protected $table = 'attendance_rfids';

    protected $fillable = [
        'student_id',
        'classroom_student_id',
        'rfid_id',
        'date',
        'checkin_time',
        'checkout_time',
        'status',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'checkin_time' => 'string',
        'checkout_time' => 'string',
        'status' => AttendanceStatusEnum::class,
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
}
