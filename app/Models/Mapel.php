<?php

namespace App\Models;

use App\Traits\Models\HasManyTeacherMapel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mapel extends Model
{
    use HasFactory, HasManyTeacherMapel, SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'mapels';
    protected $fillable = [
        'mapels_name',
    ];

}
