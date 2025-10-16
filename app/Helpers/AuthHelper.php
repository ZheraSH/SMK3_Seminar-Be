<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class AuthHelper
{
    public static function user()
    {
        return Auth::user();
    }

    public static function hasRole($role)
    {
        $user = self::user();
        return $user && $user->role === $role;
    }

    public static function hasAnyRole(array $roles)
    {
        $user = self::user();
        return $user && in_array($user->role, $roles);
    }
    public static function hasPermission($permission)
    {
        $user = self::user();
        if (!$user || !method_exists($user, 'permissions')) {
            return false;
        }
        return $user->permissions->contains('name', $permission);
    }
}
