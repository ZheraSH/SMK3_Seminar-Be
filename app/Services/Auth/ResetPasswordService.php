<?php

namespace App\Services\Auth;

use App\Helpers\ResponseHelper;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetPasswordService
{
    public function resetPassword(ResetPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return ResponseHelper::error('User tidak ditemukan', 404);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return ResponseHelper::success(null, 'Password berhasil direset');
    }
}