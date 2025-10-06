<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'schools';

    protected $fillable = [
        'NPSN',
        'phone_number',
        'image',
        'pos_code',
        'address',
        'head_school',
        'NIP',
        'website_school',
        'accreditation',
        'max_point',
    ];

    public function schoolYears()
    {
        return $this->hasMany(SchoolYear::class, 'school_id');
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class, 'school_id');
    }
}
