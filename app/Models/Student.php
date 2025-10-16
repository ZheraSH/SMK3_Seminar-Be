<?php

namespace App\Models;

use App\Enums\GenderEnum;
use App\Traits\Models\BelongsToReligion;
use App\Traits\Models\BelongsToUser;
use App\Traits\Models\HasManyClassroomStudent;
use App\Traits\Models\MorphManyRfid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Student extends Model
{
    /** @use HasFactory<\Database\Factories\StudentFactory> */
    use HasFactory, BelongsToUser,
    BelongsToReligion, HasManyClassroomStudent,
    MorphManyRfid,
    SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'students';
    protected $fillable = [
        'name',
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
    ];
    protected $cast = [
        'gender' => GenderEnum::class
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // public function feedbacks()
    // {
    //     return $this->hasMany(Feedback::class);
    // }
}
