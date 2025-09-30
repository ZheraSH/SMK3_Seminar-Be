<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelHasRfid extends Model
{
    use HasFactory;

    protected $table = 'model_has_rfid';

    protected $fillable = [
        'rfid',
        'model_type',
        'model_id',
    ];

    // Polymorphic-like reference; resolving depends on model_type values.
}
