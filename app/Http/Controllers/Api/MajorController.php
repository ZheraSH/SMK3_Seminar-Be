<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\MajorResource;
use App\Contracts\Interfaces\MajorInterface;

class MajorController extends Controller
{
    private MajorInterface $majorInteface;

    public function __construct(MajorInterface $majorInteface)
    {
        $this->majorInteface = $majorInteface;
    }

    public function index()
    {
        try {
            $data = $this->majorInteface->get();
    
            return ResponseHelper::success(
                MajorResource::collection($data),
                'Data jurusan berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 500, $th->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $data = $this->majorInteface->show($id);
    
            return ResponseHelper::success(
                new MajorResource($data),
                'Detail data jurusan berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::notFound('Data jurusan tidak ditemukan');
        }
    }
}