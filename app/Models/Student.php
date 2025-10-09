<?php

namespace App\Models;

use App\Enums\GenderEnum;
use App\Traits\Models\BelongsToReligion;
use App\Traits\Models\BelongsToSchool;
use App\Traits\Models\BelongsToUser;
use App\Traits\Models\HasManyClassroomStudent;
use App\Traits\Models\MorphManyRfid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, BelongsToUser,
    BelongsToReligion, HasManyClassroomStudent, MorphManyRfid, SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'students';
    protected $fillable = [
        'user_id',
        'nisn',
        'religion_id',
        'birth_date',
        'birth_place',
        'address',
        'nik',
        'no_kk',
        'no_birth_certificate',
        'order_child',
        'count_siblings',
        'point',
        'gender',
    ];
    protected $casts = [
        'gender' => GenderEnum::class
    ];
}
