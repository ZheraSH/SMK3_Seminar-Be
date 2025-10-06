<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuestBook extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'guest_book';

    protected $fillable = [
        'name',
        'agency',
        'purpose',
        'date',
        'arrival_time',
        'quitting_time',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
