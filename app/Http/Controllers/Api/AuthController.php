<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\Auth\LoginService;
use App\Services\Auth\LogoutService;
use App\Services\Auth\ChangePasswordService;
use App\Services\Auth\ResetPasswordService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private LoginService $loginService;
    private LogoutService $logoutService;
    private ChangePasswordService $changePasswordService;
    private ResetPasswordService $resetPasswordService;

    public function __construct(LoginService $loginService, LogoutService $logoutService, ChangePasswordService $changePasswordService, ResetPasswordService $resetPasswordService)
    {
        $this->loginService = $loginService;
        $this->logoutService = $logoutService;
        $this->changePasswordService = $changePasswordService;
        $this->resetPasswordService = $resetPasswordService;
    }

    public function login(LoginRequest $request)
    {
        try {
            return $this->loginService->login($request);
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            return $this->logoutService->logout();
        } catch (\Throwable $th) {
            return ResponseHelper::error('Logout gagal: ' . $th->getMessage(), 500);
        }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            return $this->changePasswordService->changePassword($request);
        } catch (\Throwable $th) {
            return ResponseHelper::error('Gagal mengubah password: ' . $th->getMessage(), 500);
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            return $this->resetPasswordService->resetPassword($request);
        } catch (\Throwable $th) {
            return ResponseHelper::error('Gagal mereset password: ' . $th->getMessage(), 500);
        }
    }
}