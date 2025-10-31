<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSchoolYearRequest;
use App\Http\Requests\UpdateSchoolYearRequest;
use App\Http\Resources\SchoolYearResource;
use App\Contracts\Repositories\SchoolYearRepository;
use App\Helpers\ResponseHelper;

class SchoolYearsController extends Controller
{
    private SchoolYearRepository $schoolYear;

    public function __construct(SchoolYearRepository $schoolYear)
    {
        $this->schoolYear = $schoolYear;
    }

    public function index()
    {
        $data = $this->schoolYear->get();
        return ResponseHelper::success(
            SchoolYearResource::collection($data),
            'Data tahun ajaran berhasil diambil'
        );
    }

    public function store(StoreSchoolYearRequest $request)
    {
        $data = $this->schoolYear->store($request->validated());
        return ResponseHelper::success(
            new SchoolYearResource($data),
            'Tahun ajaran berhasil ditambahkan',
            201
        );
    }

    public function show($id)
    {
        $data = $this->schoolYear->show($id);
        if (!$data) {
            return ResponseHelper::error('Data tidak ditemukan', null, 404);
        }

        return ResponseHelper::success(
            new SchoolYearResource($data),
            'Detail tahun ajaran ditemukan'
        );
    }

    public function destroy($id)
    {
        $this->schoolYear->delete($id);
        return ResponseHelper::success(
            null,
            'Data tahun ajaran berhasil dihapus'
        );
    }

    public function restore($id)
    {
        $data = $this->schoolYear->restore($id);
        if (!$data) {
            return ResponseHelper::error('Data tidak ditemukan', null, 404);
        }

        return ResponseHelper::success(
            new SchoolYearResource($data),
            'Data tahun ajaran berhasil dipulihkan'
        );
    }   

    public function active()
    {
        $data = $this->schoolYear->get()->where('active', true)->first();

        if (!$data) {
            return ResponseHelper::error('Tidak ada tahun ajaran yang aktif', null, 404);
        }

        return ResponseHelper::success(
            new SchoolYearResource($data),
            'Tahun ajaran aktif ditemukan'
        );
    }

    public function cronStatus()
    {
        $latest = \App\Models\SchoolYear::orderByDesc('created_at')->first();

        if (!$latest) {
            return response()->json([
                'status' => false,
                'message' => 'Belum ada data tahun ajaran',
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Status cron job tahun ajaran berhasil diambil',
            'data' => [
                'id' => $latest->id,
                'name' => $latest->name,
                'active' => $latest->active,
                'created_at' => $latest->created_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }
}
