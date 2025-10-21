<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSchoolYearRequest;
use App\Http\Requests\UpdateSchoolYearRequest;
use App\Http\Resources\SchoolYearResource;
use App\Contracts\Interfaces\SchoolYearInterface;
use App\Helpers\ResponseHelper;
use App\Contracts\Repositories\SchoolYearRepository;

class SchoolYearsController extends Controller
{
    private SchoolYearRepository $schoolYear;

    public function __construct(SchoolYearRepository $schoolYear)
    {
        $this->schoolYear = $schoolYear;
    }

    public function index()
    {
        $data = $this->schoolYear->all();
        return ResponseHelper::success('Data    tahun ajaran berhasil diambil', SchoolYearResource::collection($data));
    }

    public function store(StoreSchoolYearRequest $request)
    {
        $data = $this->schoolYear->store($request->validated());
        return ResponseHelper::success('Tahun ajaran berhasil ditambahkan', new SchoolYearResource($data), 201);
    }

    public function show($id)
    {
        $data = $this->schoolYear->find($id);
        if (!$data) {
            return ResponseHelper::error('Data tidak ditemukan', null, 404);
        }
        return ResponseHelper::success('Detail tahun ajaran ditemukan', new SchoolYearResource($data));
    }

    public function update(UpdateSchoolYearRequest $request, $id)
    {
        $data = $this->schoolYear->find($id);
        if (!$data) {
            return ResponseHelper::error('Data tidak ditemukan', null, 404);
        }

        $updated = $this->schoolYear->update($data, $request->validated());
        return ResponseHelper::success('Data tahun ajaran berhasil diperbarui', new SchoolYearResource($updated));
    }

    public function destroy($id)
    {
        $data = $this->schoolYear->find($id);
        if (!$data) {
            return ResponseHelper::error('Data tidak ditemukan', null, 404);
        }

        $this->schoolYear->delete($data);
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
}
