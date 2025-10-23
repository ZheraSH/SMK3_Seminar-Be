<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\MajorResource;
use App\Models\Major;


class MajorController extends Controller
{
    public function index()
    {
        try {
            $majors = Major::with('classrooms')->get();
    
            return ResponseHelper::success(
                MajorResource::collection($majors),
                'Data Jurusan Berhasil Diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $major = Major::with('classrooms')->find($id);
    
            if (! $major) {
                return ResponseHelper::notFound('Data jurusan tidak ditemukan');
            }
    
            return ResponseHelper::success(
                $major,
                'Detail Data Jurusan Berhasil Diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }
}
