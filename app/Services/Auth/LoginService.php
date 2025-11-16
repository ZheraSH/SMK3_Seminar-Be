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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LoginService
{
    public function handleLogin(LoginRequest $request)
    {
        $credentials = $request->validated();
        $cacheKey = 'login_attempt_' . md5($credentials['email']);

        if (Cache::has($cacheKey)) {
            Log::warning('Rate limit exceeded', ['email' => $credentials['email']]);
            return ResponseHelper::error('Terlalu banyak percobaan login. Coba lagi dalam 30 detik.', 429);
        }

        Cache::put($cacheKey, true, 30);

        $startTime = microtime(true);

        try {
            if ($user = $this->attemptLocalLogin($credentials)) {
                $result = $this->handleLocalLoginSuccess($user);
            } else {
                $result = $this->attemptApiLogin($credentials);
            }

            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::info('Login successful', [
                'email' => $credentials['email'],
                'type' => isset($user) ? 'local' : 'api',
                'response_time_ms' => $responseTime
            ]);

            Cache::forget($cacheKey);
            return $result;

        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            Log::error('Login failed', [
                'email' => $credentials['email'],
                'error' => $e->getMessage()
            ]);
            return ResponseHelper::error($e->getCode() ?: 500, $e->getMessage());
        }
    }

    public function handleLogout()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return ResponseHelper::error(401, 'User tidak terautentikasi');
            }

            $user->currentAccessToken()->delete();

            Log::info('Logout successful', ['user_id' => $user->id]);

            return ResponseHelper::success(
                null, 
                'Logout berhasil'
            );

        } catch (\Exception $e) {
            Log::error('Logout failed', [
                'user_id' => Auth::id() ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return ResponseHelper::error(500, 'Terjadi kesalahan saat logout: ' . $e->getMessage());
        }
    }

    private function attemptLocalLogin(array $credentials): ?User
    {
        if (!Auth::attempt($credentials)) {
            return null;
        }

        $user = Auth::user();
        $user->load(['roles']);

        return $user->roles->isNotEmpty() ? $user : null;
    }

    private function handleLocalLoginSuccess(User $user)
    {
        $role = $user->roles->first()->name;
        
        $token = $user->createToken('auth_token')->plainTextToken;

        return ResponseHelper::success([
            'user'  => $this->getUserData($user, $role),
            'role'  => $role,
            'token' => $token,
        ], 'Login berhasil');
    }

    private function attemptApiLogin(array $credentials)
    {
        $response = Http::timeout(8)
            ->retry(2, 100)
            ->post(config('api.api_login'), $credentials);

        if (!$response->successful()) {
            throw new \Exception('Kredensial salah', 401);
        }

        $data = $response->json();

        if (!($data['status'] ?? false)) {
            throw new \Exception('Login API gagal', 401);
        }

        $apiUser = $data['data']['user'] ?? null;
        if (!$apiUser) {
            throw new \Exception('Data user API tidak valid', 400);
        }

        $user = $this->syncApiUser($apiUser, $credentials['password']);
        Auth::login($user);

        $token = $user->createToken('api_auth_token')->plainTextToken;

        return ResponseHelper::success([
            'user'  => $this->getUserData($user, RoleEnum::SCHOOL->value),
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

    private function getUserData(User $user, string $role): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $role,
        ];
    }
}