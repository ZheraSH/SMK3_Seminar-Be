<?php

namespace App\Services\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class LoginService
{
    public function execute(LoginRequest $request): array
    {
        $credentials = $request->validated();

        $this->throttle($credentials['email']);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new \Exception('Email atau password salah', 422);
        }

        $user->load([
            'roles:id,name',
            'student:id,user_id,image',
            'employee:id,user_id,image',
        ]);

        if ($user->roles->isEmpty()) {
            throw new \Exception('Role user tidak ditemukan', 403);
        }

        $token = $this->generateToken($user);

        return [
            'user'  => $user,
            'role'  => $user->roles->first()->name,
            'token' => $token,
        ];
    }

    private function throttle(string $email): void
    {
        $key = 'login_attempt:' . md5($email);

        Cache::add($key, 0, 30);
        $attempts = Cache::increment($key);

        if ($attempts > 5) {
            throw new \Exception(
                'Terlalu banyak percobaan login. Coba lagi dalam 30 detik.',
                429
            );
        }
    }

    private function generateToken(User $user): string
    {
        $user->tokens()->delete();
        return $user->createToken('auth_token')->plainTextToken;
    }
}