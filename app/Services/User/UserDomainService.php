<?php

namespace App\Services\User;

use App\Models\User;
class UserDomainService
{
    public function getIdentityType(User $user): string
    {
        if ($user->student()->exists()) {
            return 'student';
        }

        if ($user->employee()->exists()) {
            return 'employee';
        }

        return 'operator';
    }
}
