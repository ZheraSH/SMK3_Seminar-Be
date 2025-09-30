<?php
// Rule.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    use HasFactory;

    protected $table = 'rules';

    protected $fillable = [
        'Violation',
        'point'
    ];

    public function studentViolations()
    {
        return $this->hasMany(StudentViolation::class, 'Rules_id');
    }
}
