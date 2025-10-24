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
        return ResponseHelper::success('Data tahun ajaran berhasil diambil', SchoolYearResource::collection($data));
    }

    public function store(StoreSchoolYearRequest $request)
    {
        $data = $this->schoolYear->store($request->validated());
        return ResponseHelper::success('Tahun ajaran berhasil ditambahkan', new SchoolYearResource($data), 201);
    }

    public function show($id)
    {
        $data = $this->schoolYear->show($id);
        if (!$data) {
            return ResponseHelper::error('Data tidak ditemukan', null, 404);
        }

        return ResponseHelper::success('Detail tahun ajaran ditemukan', new SchoolYearResource($data));
    }

    public function update(UpdateSchoolYearRequest $request, $id)
    {
        $data = $this->schoolYear->update($id, $request->validated());
        return ResponseHelper::success('Data tahun ajaran berhasil diperbarui', new SchoolYearResource($data));
    }

    public function destroy($id)
    {
        $this->schoolYear->delete($id);
        return ResponseHelper::success('Data tahun ajaran berhasil dihapus');
    }

    public function restore($id)
    {
        $data = $this->schoolYear->restore($id);
        if (!$data) {
            return ResponseHelper::error('Data tidak ditemukan', null, 404);
        }

        return ResponseHelper::success('Data tahun ajaran berhasil dipulihkan', new SchoolYearResource($data));
    }

    public function active()
    {
        $data = $this->schoolYear->get()->where('active', true)->first();

        if (!$data) {
        return ResponseHelper::error('Tidak ada tahun ajaran yang aktif', null, 404);
    }

        return ResponseHelper::success('Tahun ajaran aktif ditemukan', new SchoolYearResource($data));
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