<?php

namespace App\Models;

use App\Traits\Models\BelongsToStudent;
use App\Traits\Models\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelHasRfid extends Model
{
    use HasFactory, BelongsToStudent, MorphTo, SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'model_has_rfid';
    protected $fillable = [
        'rfid',
        'model_type',
        'student_id',
    ];
}
