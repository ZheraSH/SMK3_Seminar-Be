<?php

namespace App\Models;

use App\Enums\DayEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AttendanceRule extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';
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
