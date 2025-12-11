<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Operator\SchoolYearResource;
use App\Services\SchoolYearService;
use Illuminate\Http\Request;

class SchoolYearsController extends Controller
{
    private SchoolYearService $schoolYearService;

    public function __construct(SchoolYearService $schoolYearService)
    {
        $this->schoolYearService = $schoolYearService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->schoolYearService->paginate($request);

            return ResponseHelper::pagination(
                $data,
                SchoolYearResource::class,
                'List data tahun ajaran berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::notFound('List data tahun ajaran gagal diambil');
        }
    }

    public function store()
    {
        try {
            $data = $this->schoolYearService->storeAuto();
            return ResponseHelper::success(
                new SchoolYearResource($data),
                'Data Tahun ajaran berhasil ditambahkan',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), 400);
        }
    }

    public function destroy($id)
    {
        try {
            $this->schoolYearService->delete($id);
            return ResponseHelper::success(
                null,
                'Data Tahun ajaran berhasil dihapus'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function activate($id)
    {
        try {
            $this->schoolYearService->activate($id);
            return ResponseHelper::success(
                null,
                'Data Tahun ajaran berhasil diaktifkan'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), 400);
        }
    }

    public function active()
    {
        try {
            $data = $this->schoolYearService->active();

            return ResponseHelper::success(
                new SchoolYearResource($data),
                'Data Tahun ajaran aktif berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::notFound('Data tahun ajaran aktif gagal diambil');
        }
    }
}