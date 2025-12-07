<?php

namespace App\Services\Auth;

use App\Helpers\ResponseHelper;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LoginService
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        try {
            $this->throttle($credentials['email']);
            $user = $this->validateLocalCredentials($credentials);

            if (!$user) {
                return ResponseHelper::error('Email atau password salah', 401);
            }

            $token = $this->generateToken($user);

            return ResponseHelper::success([
                'user'  => new UserResource($user),
                'role'  => optional($user->roles->first())->name,
                'token' => $token,
            ], 'Login berhasil');

        } catch (\Exception $e) {

            if ($e->getCode() == 429) {
                return ResponseHelper::error($e->getMessage(), 429);
            }

            return ResponseHelper::error('Terjadi kesalahan saat login.', 500);
        }
    }

    public function logout()
    {
        $user = Auth::user();

        if (!$user) {
            return ResponseHelper::error('User tidak terautentikasi', 401);
        }

        $user->currentAccessToken()->delete();

        return ResponseHelper::success(null, 'Logout berhasil');
    }

    private function throttle(string $email): void
    {
        $key = 'login_attempt:' . md5($email);

        $attempts = Cache::get($key, 0);

        if ($attempts >= 5) {
            throw new \Exception('Terlalu banyak percobaan login. Coba lagi dalam 30 detik.', 429);
        }

        Cache::put($key, $attempts + 1, 30);
    }


    private function validateLocalCredentials(array $credentials): ?User
    {
        $user = $this->getUserByEmail($credentials['email']);

        if (!$user) {
            return null;
        }

        if (!$this->checkPassword($user, $credentials['password'])) {
            return null;
        }

        $this->loadUserRelations($user);

        if ($user->roles->isEmpty()) {
            return null;
        }

        return $user;
    }


    private function getUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    private function checkPassword(User $user, string $password): bool
    {
        return password_verify($password, $user->password);
    }

    private function loadUserRelations(User $user): void
    {
        $user->load([
            'roles:id,name',
            'student:id,user_id,image,nisn,gender',
            'employee:id,user_id,image,NIP,NIK,gender'
        ]);
    }

    private function generateToken(User $user): string
    {
        $user->tokens()->delete();
        return $user->createToken('auth_token')->plainTextToken;
    }
}
