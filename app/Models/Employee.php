<?php

namespace App\Models;

use App\Enums\GenderEnum;
use App\Traits\Models\BelongsToManySubjects;
use App\Traits\Models\BelongsToReligion;
use App\Traits\Models\BelongsToUser;
use App\Traits\Models\HasManyClassrooms;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Employee extends Model
{

    use HasFactory, HasUuids, BelongsToUser,
    BelongsToReligion, HasManyClassrooms,
    BelongsToManySubjects, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    protected $table = 'employees';
    protected $fillable = [
        'user_id',
        'image',
        'nip',
        'nik',
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