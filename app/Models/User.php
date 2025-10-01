<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)
                    ->withPivot('assigned_by')
                    ->withTimestamps();
    }

    public function hasRole($roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    public function subRoles()
    {
        return $this->belongsToMany(SubRole::class, 'user_sub_roles')
                    ->withPivot('assigned_by')
                    ->withTimestamps();
    }

    public function hasSubRole($subRoleName): bool
    {
        return $this->subRoles()->where('name', $subRoleName)->exists();
    }

    public function hasAnySubRole(array $subRoleNames): bool
    {
        return $this->subRoles()->whereIn('name', $subRoleNames)->exists();
    }
}
