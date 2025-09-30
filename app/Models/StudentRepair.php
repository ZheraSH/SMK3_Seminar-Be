<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentRepair extends Model
{
    use HasFactory;

    protected $table = 'student_repairs';

    protected $fillable = [
        'Student_id',
        'Repair_id',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'Student_id');
    }

    public function repair()
    {
        return $this->belongsTo(Repair::class, 'Repair_id');
    }
}
