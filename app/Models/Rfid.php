<?php

namespace App\Models;

use App\Enums\RfidStatusEnum;
use App\Traits\Models\BelongsToStudent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rfid extends Model
{
    use HasFactory, BelongsToStudent, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'rfids';

    protected $fillable = [
        'rfid',
        'status',
        'student_id',
    ];

    protected $casts = [
        'status' => RfidStatusEnum::class,
    ];
}