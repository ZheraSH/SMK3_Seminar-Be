<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Operator\MajorResource;
use App\Contracts\Repositories\MajorRepository;

class MajorController extends Controller
{
    private MajorRepository $majorRepository;

    public function __construct(MajorRepository $majorRepository)
    {
        $this->majorRepository = $majorRepository;
    }

    public function index()
    {
        try {
            $data = $this->majorRepository->get();

            return ResponseHelper::success(
                MajorResource::collection($data),
                'Data jurusan berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(),$th->getCode() ?: 500);
        }
    }
}