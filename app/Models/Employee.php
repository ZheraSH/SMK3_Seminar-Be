<?php

namespace App\Models;

use App\Enums\GenderEnum;
use App\Traits\Models\BelongsToReligion;
use App\Traits\Models\BelongsToUser;
use App\Traits\Models\HasManyClassrooms;
// use App\Traits\Models\MorphManyAttendance;
use App\Traits\Models\MorphManyRfid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{

    use HasFactory, BelongsToUser,
    BelongsToReligion, HasManyClassrooms,
    // MorphManyAttendance,
    MorphManyRfid, 
    SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
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
        'active' =>'boolean',
    ];
}
