<?php

namespace App\Models;

use App\Traits\Models\BelongsToUser;
use App\Traits\Models\HasManyAttendanceRule;
use App\Traits\Models\HasManyEmployee;
use App\Traits\Models\HasManyLessonHour;
use App\Traits\Models\HasManyLevelClass;
use App\Traits\Models\HasManyMaple;
use App\Traits\Models\HasManyModelHasRfid;
use App\Traits\Models\HasManySchoolYear;
use App\Traits\Models\HasManySemester;
use App\Traits\Models\HasManyStudent;
use App\Traits\Models\MorphManyRfid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use HasFactory, BelongsToUser,
    HasManyStudent, HasManyEmployee,
    HasManySchoolYear, HasManyLessonHour,
    HasManyLevelClass, MorphManyRfid,
    HasManyModelHasRfid, SoftDeletes;


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
}
