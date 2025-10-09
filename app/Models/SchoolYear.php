<?php

namespace App\Models;

use App\Traits\Models\HasManyClassroom;
use App\Traits\Models\HasManyLessonSchedule;
use App\Traits\Models\HasManyMapel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolYear extends Model
{
    use HasFactory, HasManyMapel, HasManyLessonSchedule, HasManyClassroom, SoftDeletes;

    protected $guarded = ['id'];
}

