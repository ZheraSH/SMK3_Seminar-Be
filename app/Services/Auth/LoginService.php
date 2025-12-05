<?php

namespace App\Services\Auth;

use App\Helpers\ResponseHelper;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LoginService
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $this->throttle($credentials['email']);

        try {
            $user = $this->validateLocalCredentials($credentials);

            if (!$user) {
                return ResponseHelper::error('Email atau password salah', 401);
            }

            $token = $this->generateToken($user);

            return ResponseHelper::success([
                'user'  => $this->mapUser($user),
                'role'  => optional($user->roles->first())->name,
                'token' => $token,
            ], 'Login berhasil');

        } catch (\Throwable $e) {
            Log::error('Login failed', [
                'email' => $credentials['email'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return ResponseHelper::error('Terjadi kesalahan saat login', 500);
        }
    }

    public function logout()
    {
        $user = Auth::user();

        if (!$user) {
            return ResponseHelper::error('User tidak terautentikasi', 401);
        }

        $user->currentAccessToken()->delete();

        Log::info('Logout successful', ['user_id' => $user->id]);

        return ResponseHelper::success(null, 'Logout berhasil');
    }

    private function throttle(string $email): void
    {
        $key = 'login_attempt:' . md5($email);

        if (Cache::has($key)) {
            throw new \Exception('Terlalu banyak percobaan login. Coba lagi dalam 30 detik.', 429);
        }

        Cache::put($key, true, 30);
    }

    private function validateLocalCredentials(array $credentials): ?User
    {
        if (!Auth::attempt($credentials)) {
            return null;
        }

        $user = Auth::user();
        $user->load(['roles', 'student', 'employee']);

        if ($user->roles->isEmpty()) {
            return null;
        }

        return $user;
    }

    private function generateToken(User $user): string
    {
        return $user->createToken('auth_token')->plainTextToken;
    }

    private function mapUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'employee_id' => $user->employee?->id,
            'student_id' => $user->student?->id,
            'roles' => $user->roles->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name
            ]),
        ];
    }
}
