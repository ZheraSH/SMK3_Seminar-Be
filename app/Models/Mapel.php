<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mapel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mapels';

    protected $fillable = [
        'mapels_name',
        'religion_id'
    ];

    public function teacherMapels()
    {
        return $this->hasMany(TeacherMapel::class, 'mapel_id');
    }

    public function religion()
    {
        return $this->belongsTo(Religion::class, 'religion_id');
    }
}
