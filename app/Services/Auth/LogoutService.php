<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Auth;

class LogoutService
{
    public function execute(): void
    {
        $user = Auth::user();

        if (!$user) {
            throw new \Exception('User tidak terautentikasi', 401);
        }

        $user->currentAccessToken()->delete();
    }
}