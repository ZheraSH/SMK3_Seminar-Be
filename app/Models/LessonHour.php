<?php

namespace App\Models;

use App\Enums\DayEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class LessonHour extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'lesson_hours';
    protected $fillable = [
        'day',
        'name',
        'start',
        'end',
        'is_lesson'
    ];

    protected $casts = [
        'day' => DayEnum::class,
    ];
}
