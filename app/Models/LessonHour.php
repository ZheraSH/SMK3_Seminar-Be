<?php

namespace App\Models;

use App\Enums\DayEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class LessonHour extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

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
