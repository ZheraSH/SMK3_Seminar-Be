<?php
// StudentViolation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentViolation extends Model
{
    use HasFactory;

    protected $table = 'student_violations';

    protected $fillable = [
        'Student_id',
        'Rules_id'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'Student_id');
    }

    public function rule()
    {
        return $this->belongsTo(Rule::class, 'Rules_id');
    }
}
