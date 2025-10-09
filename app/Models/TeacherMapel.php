<?php

namespace App\Models;

use App\Traits\Models\BelongsToEmployee;
use App\Traits\Models\BelongsToMapel;
use App\Traits\Models\HasManyLessonSchedule;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherMapel extends Model
{
    use HasFactory, BelongsToEmployee, BelongsToMapel, SoftDeletes;

    protected $guared = ['id'];
    protected $table = 'teacher_mapels';
    protected $fillable = [
        'mapel_id',
        'employee_id',
        'school_year_id',
    ];
    public function lessonSchedules()
    {
        return $this->hasMany(LessonSchedule::class, 'teacher_mapel_id');
    }
}
