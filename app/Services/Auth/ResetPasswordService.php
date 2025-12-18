<?php

namespace App\Services\Auth;

use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetPasswordService
{
    public function execute(ResetPasswordRequest $request): void
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw new \Exception('User tidak ditemukan', 404);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);
    }
}