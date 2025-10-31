<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\SemesterResource;
use App\Models\Semester;

class SemesterController extends Controller
{
    public function index()
    {
        try {
            $semesters = Semester::all();

            return ResponseHelper::success(
                SemesterResource::collection($semesters),
                'Data Semester Berhasil Diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $semester = Semester::findOrFail($id);

            if (!$semester) {
                return ResponseHelper::notFound('Data Semester Tidak Ditemukan');
            }

            return ResponseHelper::success(
                new SemesterResource($semester),
                'Detail Data Semester Berhasil Diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function active()
    {
        try {
            $semester = Semester::where('active', true)->first();

            if (!$semester) {
                return ResponseHelper::notFound('Tidak Ada Semester Aktif');
            }

            return ResponseHelper::success(
                new SemesterResource($semester),
                'Data Semester Aktif Berhasil Diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }

    public function cronStatus()
    {
        try {
            $activeSemester = Semester::where('active', true)->first();

            $status = $activeSemester
                ? "Semester aktif: " . $activeSemester->name
                : "Tidak ada semester aktif";

            return ResponseHelper::success(
                ['status' => $status],
                'Status Semester Berhasil Diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }
}
