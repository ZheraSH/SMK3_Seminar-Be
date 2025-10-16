<?php

namespace App\Services\Auth;

use App\Enums\RoleEnum;
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
                return $this->jsonError('User tidak memiliki role', 403);
            }

            $role = $user->roles->pluck('name')->first();
            $token = $user->createToken($user->email)->plainTextToken;

            return $this->jsonSuccess('Login berhasil', $user, $role, $token);
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
            return $this->jsonError('Kredensial salah', 401);
        }

        $data = $response->json();

        if (! ($data['status'] ?? false) || empty($data['data']['token'])) {
            return $this->jsonError('Login API gagal', 401);
        }

        $apiUser = $data['data']['user'] ?? null;
        if (! $apiUser) {
            return $this->jsonError('Data user API tidak valid', 400);
        }

        $user = $this->syncApiUser($apiUser, $credentials['password']);
        Auth::login($user);

        $token = $user->createToken($user->email)->plainTextToken;

        return $this->jsonSuccess('Login API berhasil', $user, RoleEnum::SCHOOL->value, $token);
    }

    private function syncApiUser(array $apiUser, string $password): User
    {
        $user = User::updateOrCreate(
            ['id' => $apiUser['id']],
            [
                'name' => $apiUser['name'],
                'slug' => Str::slug($apiUser['name']),
                'email' => $apiUser['email'],
                'password' => Hash::make($password),
            ]
        );
    
        $user->syncRoles([RoleEnum::SCHOOL->value]);
        return $user;
    }
    

    private function jsonError(string $message, int $status)
    {
        return response()->json(['message' => $message], $status);
    }

    private function jsonSuccess(string $message, User $user, string $role, string $token)
    {
        return response()->json([
            'message' => $message,
            'role'    => $role,
            'user'    => $user,
            'token'   => $token,
        ]);
    }
}
