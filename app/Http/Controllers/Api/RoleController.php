<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Contracts\Repositories\Operator\RoleRepository;
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
            $data = $this->roleRepository->get();

            return ResponseHelper::success(
                RoleResource::collection($data),
                'List data role berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::notFound('List data role gagal diambil');
        }
    }
}