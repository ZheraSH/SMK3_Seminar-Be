<?php

namespace App\Services\Auth;

use App\Http\Requests\Auth\ChangePasswordRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChangePasswordService
{
    public function execute(ChangePasswordRequest $request): void
    {
        $user = Auth::user();

        if (!$user) {
            throw new \Exception('User tidak terautentikasi', 401);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            throw new \Exception('Password lama tidak sesuai', 422);
        }

        $user->update(['password' => Hash::make($request->new_password),
        ]);
    }
}