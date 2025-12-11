<?php

namespace App\Services\Auth;

use App\Helpers\ResponseHelper;
use App\Http\Requests\Auth\ChangePasswordRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChangePasswordService
{
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = Auth::user();

        if (!$user) {
            return ResponseHelper::error('User tidak terautentikasi', 401);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return ResponseHelper::error('Password lama tidak sesuai', 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return ResponseHelper::success(null, 'Password berhasil diubah');
    }
}