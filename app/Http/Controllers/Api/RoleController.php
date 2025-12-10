<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Contracts\Repositories\RoleRepository;
use App\Http\Resources\Operator\RoleResource;
use App\Helpers\ResponseHelper;

class RoleController extends Controller
{
    private RoleRepository $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function index()
    {
        try {
            $roles = $this->roleRepository->get();

            return ResponseHelper::success(
                RoleResource::collection($roles),
                'Daftar role berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(),$th->getCode() ?: 500);
        }
    }
}