<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReligionResource;
use App\Contracts\Interfaces\ReligionInterface;

class ReligionController extends Controller
{
    private ReligionInterface $religionInterface;

    public function __construct(ReligionInterface $religionInterface)
    {
        $this->religionInterface = $religionInterface;
    }

    public function index()
    {
        try {
            $data = $this->religionInterface->get();

            return ResponseHelper::success(
                ReligionResource::collection($data),
                'Data agama berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(),$th->getCode() ?: 500);
        }
    }
}