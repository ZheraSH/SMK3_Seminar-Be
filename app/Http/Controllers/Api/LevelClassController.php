<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\LevelClassResource;
use App\Contracts\Interfaces\LevelClassInterface;

class LevelClassController extends Controller
{
    private LevelClassInterface $levelClassInterface;

    public function __construct(LevelClassInterface $levelClassInterface)
    {
        $this->levelClassInterface = $levelClassInterface;
    }

    public function index()
    {
        try {
            $data = $this->levelClassInterface->get();

            return ResponseHelper::success(
                LevelClassResource::collection($data),
                'Data tingkat kelas berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(),$th->getCode() ?: 500);
        }
    }
}