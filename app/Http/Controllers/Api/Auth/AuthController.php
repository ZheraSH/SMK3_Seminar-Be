<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Services\Auth\LoginService;
use App\Services\Auth\LogoutService;
use App\Services\Auth\ChangePasswordService;
use App\Services\Auth\ResetPasswordService;

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
            $result = $this->loginService->execute($request);
    
            return ResponseHelper::success(new LoginResource($result), 'Login berhasil');
        } catch (\Throwable $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode() ?: 400);
        }
    }    

    public function logout()
    {
        try {
            $this->logoutService->execute();

            return ResponseHelper::success(null, 'Logout berhasil');
        } catch (\Throwable $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $this->changePasswordService->execute($request);

            return ResponseHelper::success(null, 'Password berhasil diubah');
        } catch (\Throwable $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $this->resetPasswordService->execute($request);

            return ResponseHelper::success(null, 'Password berhasil direset');
        } catch (\Throwable $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}