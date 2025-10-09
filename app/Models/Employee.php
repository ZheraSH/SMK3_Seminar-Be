<?php

namespace App\Models;

use App\Enums\GenderEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Models\HasManyExtracurricular;
use App\Traits\Models\HasManyTeacherSubject;
use App\Traits\Models\BelongsToReligion;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Models\HasManyClassroom;
use App\Traits\Models\BelongsToUser;
use App\Traits\Models\HasManyStudentViolation;
use App\Traits\Models\MorphManyAttendance;
use App\Traits\Models\MorphManyRfid;

use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, BelongsToUser,
    BelongsToReligion,HasManyClassroom,
    MorphManyAttendance, MorphManyRfid, SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'employees';
    protected $fillable = [
        'user_id',
        'image',
        'NIP',
        'NIK',
        'religion_id',
        'gender',
        'birth_date',
        'birth_place',
        'address',
        'phone_number',
    ];
    protected $casts = [
        'gender' => GenderEnum::class,
    ];
}
