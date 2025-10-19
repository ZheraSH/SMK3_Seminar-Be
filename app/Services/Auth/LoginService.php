<?php

namespace App\Services\Auth;

use App\Enums\RoleEnum;
use App\Helpers\ResponseHelper;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LoginService
{
    public function handleLogin(LoginRequest $request)
    {
        $credentials = $request->validated();

        if ($this->attemptLocalLogin($credentials)) {
            $user = Auth::user();

            if (! $user->roles()->exists()) {
                return ResponseHelper::error('User tidak memiliki role', 403);
            }

            $role = $user->roles->pluck('name')->first();
            $token = $user->createToken($user->email)->plainTextToken;

            return ResponseHelper::success([
                'user'  => $user,
                'role'  => $role,
                'token' => $token,
            ], 'Login berhasil');
        }

        return $this->attemptApiLogin($credentials);
    }

    private function attemptLocalLogin(array $credentials): bool
    {
        return Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ]);
    }

    private function attemptApiLogin(array $credentials)
    {
        $response = Http::post(config('api.api_login'), $credentials);

        if (! $response->successful()) {
            return ResponseHelper::error('Kredensial salah', 401);
        }

        $data = $response->json();

        if (! ($data['status'] ?? false) || empty($data['data']['token'])) {
            return ResponseHelper::error('Login API gagal', 401);
        }

        $apiUser = $data['data']['user'] ?? null;
        if (! $apiUser) {
            return ResponseHelper::error('Data user API tidak valid', 400);
        }

        $user = $this->syncApiUser($apiUser, $credentials['password']);
        Auth::login($user);

        $token = $user->createToken($user->email)->plainTextToken;

        return ResponseHelper::success([
            'user'  => $user,
            'role'  => RoleEnum::SCHOOL->value,
            'token' => $token,
        ], 'Login API berhasil');
    }

    private function syncApiUser(array $apiUser, string $password): User
    {
        $user = User::updateOrCreate(
            ['email' => $apiUser['email']],
            [
                'uuid' => $apiUser['uuid'] ?? Str::uuid(),
                'name' => $apiUser['name'],
                'slug' => Str::slug($apiUser['name']),
                'password' => Hash::make($password),
            ]
        );

        $user->syncRoles([RoleEnum::SCHOOL->value]);
        return $user;
    }
}
