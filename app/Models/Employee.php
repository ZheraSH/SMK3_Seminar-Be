<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $fillable = [
        'user_id',
        'NIP',
        'NIK',
        'agama',
        'birthdate',
        'birthplace',
        'phone_number',
        'address',
        'employment_status',
        'religion_id',
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function religion()
    {
        return $this->belongsTo(Religion::class, 'religion_id');
    }

    public function teacherMapels()
    {
        return $this->hasMany(TeacherMapel::class, 'employee_id');
    }

    public function positions()
    {
        return $this->hasMany(EmployeePosition::class, 'employee_id');
    }
}
