<?php

namespace App\Services\Contracts;

use App\Models\User;

interface AuthServiceInterface
{
    public function register(array $payload): array;
    public function login(array $payload): array;
    public function registerStudent(array $payload): array;
    public function registerEmployee(array $payload): array;
    public function me(User $user): User;
    public function logout(User $user): void;
}


