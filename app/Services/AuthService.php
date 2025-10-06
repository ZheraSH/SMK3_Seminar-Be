<?php

namespace App\Services;

use App\Helpers\CrudHelper;
use App\Helpers\ResponseHelper;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceInterface
{
    public function register(array $payload): array
    {
        $user = User::create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => Hash::make($payload['password']),
            'gender' => $payload['gender'] ?? null,
        ]);

        if (!empty($payload['role'])) {
            $user->assignRole($payload['role']);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $user->load(['roles']);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function login(array $payload): array
    {
        if (!Auth::attempt(['email' => $payload['email'], 'password' => $payload['password']])) {
            return ['error' => 'Invalid credentials'];
        }

        $user = User::where('email', $payload['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;
        $user->load(['roles']);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function me(User $user): User
    {
        return $user->load(['roles', 'student', 'employee']);
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function registerStudent(array $payload): array
    {
        $studentRole = 'siswa';

        $user = User::create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => Hash::make($payload['password']),
            'gender' => $payload['gender'] ?? null,
        ]);

        $user->assignRole($studentRole);

        $student = CrudHelper::create(new Student, [
            'user_id' => $user->id,
            'nisn' => $payload['nisn'],
            'religion_id' => $payload['religion_id'] ?? null,
            'birthdate' => $payload['birthdate'] ?? null,
            'birthplace' => $payload['birthplace'] ?? null,
            'address' => $payload['address'] ?? null,
            'nik' => $payload['nik'] ?? null,
            'no_kk' => $payload['no_kk'] ?? null,
            'no_birth_certificate' => $payload['no_birth_certificate'] ?? null,
            'order_child' => $payload['order_child'] ?? null,
            'count_siblings' => $payload['count_siblings'] ?? null,
            'point' => $payload['point'] ?? 0,
        ]);

        if (!$student) {
            $user->delete();
            return ['error' => 'Failed to create student profile'];
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $user->load(['roles', 'student']);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function registerEmployee(array $payload): array
    {
        $roleName = $payload['role'] ?? null;
        if (!$roleName || !in_array($roleName, ['guru', 'staff TU'])) {
            return ['error' => 'Role must be guru or staff TU'];
        }

        $user = User::create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => Hash::make($payload['password']),
            'gender' => $payload['gender'] ?? null,
        ]);

        $user->assignRole($roleName);

        $employee = CrudHelper::create(new Employee, [
            'user_id' => $user->id,
            'nip' => $payload['nip'],
            'employment_status' => $payload['employment_status'],
            'religion_id' => $payload['religion_id'] ?? null,
        ]);

        if (!$employee) {
            $user->delete();
            return ['error' => 'Failed to create employee profile'];
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $user->load(['roles', 'employee']);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}


