<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserCacheService
{
    public function cachedRoles(User $user)
    {
        return Cache::remember("user_{$user->id}_roles", 3600, fn() =>
            $user->roles()->get()
        );
    }
}
