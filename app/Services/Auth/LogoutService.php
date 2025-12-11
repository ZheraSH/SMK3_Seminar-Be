<?php

namespace App\Services\Auth;

use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Auth;

class LogoutService
{
    public function logout()
    {
        $user = Auth::user();

        if (!$user) {
            return ResponseHelper::error('User tidak terautentikasi', 401);
        }

        $user->currentAccessToken()->delete();

        return ResponseHelper::success(null, 'Logout berhasil');
    }
}