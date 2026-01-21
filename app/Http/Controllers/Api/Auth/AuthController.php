<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Profile\UpdateEmailRequest;
use App\Http\Requests\Profile\UpdatePhotoRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Http\Resources\Profile\ProfileResource;
use App\Services\Auth\LoginService;
use App\Services\Auth\LogoutService;
use App\Services\Auth\ChangePasswordService;
use App\Services\Auth\ResetPasswordService;
use App\Services\Profile\UpdateEmailService;
use App\Services\Profile\UpdatePhotoService;

class AuthController extends Controller
{
    private LoginService $loginService;
    private LogoutService $logoutService;
    private ChangePasswordService $changePasswordService;
    private ResetPasswordService $resetPasswordService;
    private UpdateEmailService $updateEmailService;
    private UpdatePhotoService $updatePhotoService;

    public function __construct(
        LoginService $loginService,
        LogoutService $logoutService,
        ChangePasswordService $changePasswordService,
        ResetPasswordService $resetPasswordService,
        UpdateEmailService $updateEmailService,
        UpdatePhotoService $updatePhotoService
    ) {
        $this->loginService = $loginService;
        $this->logoutService = $logoutService;
        $this->changePasswordService = $changePasswordService;
        $this->resetPasswordService = $resetPasswordService;
        $this->updateEmailService = $updateEmailService;
        $this->updatePhotoService = $updatePhotoService;
    }

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->loginService->execute($request);

            return ResponseHelper::success(new LoginResource($result), 'Login berhasil');
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function logout()
    {
        try {
            $this->logoutService->execute();

            return ResponseHelper::success(null, 'Logout berhasil');
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $this->changePasswordService->execute($request);

            return ResponseHelper::success(null, 'Password berhasil diubah');
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $this->resetPasswordService->execute($request);

            return ResponseHelper::success(null, 'Password berhasil direset');
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function changeEmail(UpdateEmailRequest $request)
    {
        try {
            $this->updateEmailService->execute($request);

            return ResponseHelper::success(
                new ProfileResource(auth()->user()->fresh()),
                'Email berhasil diperbarui'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function changePhoto(UpdatePhotoRequest $request)
    {
        try {
            $this->updatePhotoService->execute($request);

            return ResponseHelper::success(
                new ProfileResource(auth()->user()->fresh(['student', 'employee'])),
                'Foto profil berhasil diperbarui'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function profile()
    {
        try {
            $user = auth()->user()->fresh(['student', 'employee']);
            return ResponseHelper::success(
                new ProfileResource($user),
                'Profil berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}
