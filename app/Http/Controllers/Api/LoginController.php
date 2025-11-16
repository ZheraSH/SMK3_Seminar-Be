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
            $result = $this->loginService->handleLogin($request);
            return $result;

        } catch (\Throwable $th) {
            return ResponseHelper::error(
                $th->getCode() ?: 500, 
                $th->getMessage()
            );
        }
    }

    public function logout(Request $request)
    {
        try {
            $result = $this->loginService->handleLogout();
            return $result;

        } catch (\Throwable $th) {
            return ResponseHelper::error(
                500, 
                'Logout gagal: ' . $th->getMessage()
            );
        }
    }

}