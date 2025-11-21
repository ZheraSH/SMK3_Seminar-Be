<?php

namespace App\Models;

use App\Enums\PermissionStatusEnum;
use App\Enums\PermissionTypeEnum;
use App\Traits\Models\BelongsToCounselor;
use App\Traits\Models\BelongsToStudent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendancePermission extends Model
{
    use HasFactory, BelongsToStudent, BelongsToCounselor, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'attendance_permissions';
    protected $fillable = [
        'type',
        'start_date',
        'end_date',
        'reason',
        'proof',
        'status',
        'student_id',
        'counselor_id',
        'verified_at',
    ];

    protected $casts = [
        'type' => PermissionTypeEnum::class,
        'status' => PermissionStatusEnum::class,
        'start_date' => 'date',
        'end_date' => 'date',
        'verified_at' => 'datetime',
    ];
}
