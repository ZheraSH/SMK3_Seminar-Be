<?php

namespace App\Models;

use App\Models\LessonSchedule;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Models\BelongsToEmployee;
use App\Traits\Models\BelongsToLevelClass;
use App\Traits\Models\BelongsToSchoolYear;
use App\Traits\Models\HasManyClassroomStudent;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classroom extends Model
{
    use HasFactory, BelongsToEmployee, BelongsToLevelClass, BelongsToSchoolYear, HasManyClassroomStudent, SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'classrooms';
    protected $fillable = [
        'major_id',
        'slug',
        'level_class_id',
        'employee_id',
        'school_year_id',
    ];

    public $incrementing = false;
    public $keyType = 'char';

    public function lessonSchedules()
    {
        return $this->hasMany(LessonSchedule::class, 'classroom_id');
    }
}
