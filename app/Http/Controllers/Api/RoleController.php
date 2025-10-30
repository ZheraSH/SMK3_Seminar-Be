<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use App\Services\RoleService;
use Throwable;

class RoleController extends Controller
{
    private RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index()
    {
        try {
            $roles = $this->roleService->getAllRoles();

            return ResponseHelper::success($roles, 'Daftar role berhasil diambil');
        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }
}