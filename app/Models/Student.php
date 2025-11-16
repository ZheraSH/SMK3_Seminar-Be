<?php

namespace App\Models;

use App\Enums\GenderEnum;
use App\Enums\StudentStatusEnum;
use App\Traits\Models\BelongsToReligion;
use App\Traits\Models\HasOneRfid;
use App\Traits\Models\BelongsToUser;
use App\Traits\Models\StudentHasManyClassroomStudents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{

    use HasFactory, BelongsToUser,
    BelongsToReligion, StudentHasManyClassroomStudents,
    HasOneRfid, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'students';
    protected $fillable = [
        'user_id',
        'image',
        'nisn',
        'religion_id',
        'gender',
        'birth_date',
        'birth_place',
        'address',
        'number_kk',
        'number_akta',
        'order_child',
        'count_siblings',
        'status'
    ];
    protected $casts = [
        'gender' => GenderEnum::class,
        'status' => StudentStatusEnum::class
    ];
}
