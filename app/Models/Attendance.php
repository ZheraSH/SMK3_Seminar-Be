<?php

namespace App\Models;

use App\Enums\AttendanceEnum;
use App\Traits\Models\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, MorphTo, SoftDeletes;

    public $casts = [
        'status' => AttendanceEnum::class
    ];
    protected $guarded = ['id'];
    protected $table = 'attendance';
    protected $fillable = [
        'classroom_student_id',
        'model_type',
        'model_id',
        'point',
        'status',
        'proof',
        'chekin',
        'chekout',
    ];
}
