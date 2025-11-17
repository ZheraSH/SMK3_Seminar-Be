<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\Auth\LoginService;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    private LoginService $loginService;

    public function __construct(LoginService $loginService)
    {
        $this->loginService = $loginService;
    }

    public function login(LoginRequest $request)
    {
        try {
            return $this->loginService->login($request);

        } catch (\Throwable $th) {
            return ResponseHelper::error(
                $th->getCode(),
                $th->getMessage()?: 500
            );
        }
    }

    public function logout(Request $request)
    {
        try {
            return $this->loginService->logout();

        } catch (\Throwable $th) {
            return ResponseHelper::error(
                500,
                'Logout gagal: ' . $th->getMessage(),
            );
        }
    }
}