<?php

namespace App\Models;

use App\Enums\DayEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceRule extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'attendance_rules';
    protected $fillable = [
        'day',
        'checkin_start',
        'checkin_end',
        'checkout_start',
        'checkout_end',
        'is_holiday',
    ];

    protected $casts = [
        'day' => DayEnum::class,
        'is_holiday' => 'boolean',
    ];
}
