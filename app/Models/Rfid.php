<?php

namespace App\Models;

use App\Enums\RfidStatusEnum;
use App\Traits\Models\BelongsToStudent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Rfid extends Model
{
    use HasFactory, HasUuids, BelongsToStudent, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';
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