<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\LevelClassResource;
use App\Models\LevelClass;

class LevelClassController extends Controller
{
    public function index()
    {
        try {
            $levelClass = LevelClass::all();
            
            return ResponseHelper::success(
                LevelClassResource::collection($levelClass),
                'Data LevelClass Berhasil Diambil'
                );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $levelClass = LevelClass::findOrFail($id);

            if (! $levelClass) {
                return ResponseHelper::notFound('Data LevelClass tidak ditemukan');
            }

            return ResponseHelper::success(
                new LevelClassResource($levelClass),
                'Detail Data LevelClass Berhasil Diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }
}
