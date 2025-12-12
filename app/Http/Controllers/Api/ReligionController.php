<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Contracts\Repositories\Operator\ReligionRepository;
use App\Http\Resources\Operator\ReligionResource;
use App\Helpers\ResponseHelper;

class ReligionController extends Controller
{
    private ReligionRepository $religionRepository;

    public function __construct(ReligionRepository $religionRepository)
    {
        $this->religionRepository = $religionRepository;
    }

    public function index()
    {
        try {
            $data = $this->religionRepository->get();

            return ResponseHelper::success(
                ReligionResource::collection($data),
                'List data agama berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::notFound('List data agama gagal diambil');
        }
    }
}