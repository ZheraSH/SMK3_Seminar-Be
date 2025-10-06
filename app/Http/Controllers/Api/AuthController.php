<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterStudentRequest;
use App\Http\Requests\Auth\RegisterEmployeeRequest;
use App\Services\Contracts\AuthServiceInterface;

class AuthController extends Controller
{
    public function __construct(private AuthServiceInterface $authService) {}

    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated());
        if (isset($result['error'])) {
            return ResponseHelper::error($result['error'], 422);
        }
        return ResponseHelper::success($result, 'User registered successfully');
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());
        if (isset($result['error'])) {
            return ResponseHelper::error('Invalid credentials', 401);
        }
        return ResponseHelper::success($result, 'Login successful');
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return ResponseHelper::success(null, 'Logout successful');
    }

    public function me(Request $request)
    {
        $user = $this->authService->me($request->user());
        return ResponseHelper::success($user, 'User profile retrieved successfully');
    }

    public function registerStudent(RegisterStudentRequest $request)
    {
        $result = $this->authService->registerStudent($request->validated());
        if (isset($result['error'])) {
            $message = $result['error'] === 'Student role not found' ? 'Student role not found' : 'Failed to create student profile';
            $code = $result['error'] === 'Student role not found' ? 404 : 400;
            return ResponseHelper::error($message, $code);
        }
        return ResponseHelper::success($result, 'Student registered successfully');
    }

    public function registerEmployee(RegisterEmployeeRequest $request)
    {
        $result = $this->authService->registerEmployee($request->validated());
        if (isset($result['error'])) {
            $message = $result['error'] === 'Role must be guru or staff TU' ? $result['error'] : 'Failed to create employee profile';
            $code = $result['error'] === 'Role must be guru or staff TU' ? 422 : 400;
            return ResponseHelper::error($message, $code);
        }
        return ResponseHelper::success($result, 'Employee registered successfully');
    }
}