<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\MajorResource;
use App\Contracts\Interfaces\MajorInterface;

class MajorController extends Controller
{
    private MajorInterface $majorInterface;

    public function __construct(MajorInterface $majorInterface)
    {
        $this->majorInterface = $majorInterface;
    }

    public function index()
    {
        try {
            $data = $this->majorInterface->get();

            return ResponseHelper::success(
                MajorResource::collection($data),
                'Data jurusan berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(),$th->getCode() ?: 500);
        }
    }

    public function show(string $id)
    {
        try {
            $data = $this->majorInterface->show($id);

            return ResponseHelper::success(
                new MajorResource($data),
                'Detail data jurusan berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error('Data jurusan tidak ditemukan',404);
        }
    }
}