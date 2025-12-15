<?php

namespace App\Models;

use App\Enums\AccreditationEnum;
use App\Enums\SchoolTypeEnum;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class School extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    protected $table = 'schools';
    protected $fillable = [
        'logo',
        'name',
        'principal_name',
        'npsn',
        'phone',
        'email',
        'school_type',
        'accreditation',
        'address',
    ];
    protected $casts = [
        'school_type' => SchoolTypeEnum::class,
        'accreditation' => AccreditationEnum::class,
    ];
}
