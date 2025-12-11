<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Operator\LevelClassResource;
use App\Contracts\Repositories\LevelClassRepository;

class LevelClassController extends Controller
{
    private LevelClassRepository $levelClassRepository;

    public function __construct(LevelClassRepository $levelClassRepository)
    {
        $this->levelClassRepository = $levelClassRepository;
    }

    public function index()
    {
        try {
            $data = $this->levelClassRepository->get();

            return ResponseHelper::success(
                LevelClassResource::collection($data),
                'List data tingkat kelas berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::notFound('List data tingkat kelas gagal diambil');
        }
    }
}