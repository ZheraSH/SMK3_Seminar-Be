<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReligionResource;
use App\Models\Religion;

class ReligionController extends Controller
{
    public function index()
    {
        try{
            $religions = Religion::all();

            return ResponseHelper::success(
                ReligionResource::collection($religions),
                'Data Religion Berhasil Diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $religion = Religion::findOrFail($id);

            if (!$religion) {
                return ResponseHelper::notFound('Data Religion tidak ditemukan');
            }
            return ResponseHelper::success(
                new ReligionResource($religion),
                'Detail Data Religion Berhasil Diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }
}
