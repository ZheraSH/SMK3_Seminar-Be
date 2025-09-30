<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Religion extends Model
{
    use HasFactory;

    protected $table = 'religions';

    protected $fillable = [
        'name'
    ];

    public function students()
    {
        return $this->hasMany(Student::class, 'religion_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'religion_id');
    }
}
