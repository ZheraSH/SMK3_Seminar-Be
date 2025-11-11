<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Contracts\Repositories\SchoolYearRepository;
use App\Http\Requests\StoreSchoolYearRequest;
use App\Http\Requests\UpdateSchoolYearRequest;
use App\Http\Resources\SchoolYearResource;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Throwable;

class SchoolYearsController extends Controller
{
    private SchoolYearRepository $schoolYear;

    public function __construct(SchoolYearRepository $schoolYear)
    {
        $this->schoolYear = $schoolYear;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->schoolYear->search($request, 12);

            return ResponseHelper::success(
                SchoolYearResource::collection($data),
                'Data tahun ajaran berhasil diambil'
            );
        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function store(StoreSchoolYearRequest $request)
    {
        try {
            $data = $this->schoolYear->store($request->validated());

            return ResponseHelper::success(
                new SchoolYearResource($data),
                'Tahun ajaran berhasil ditambahkan',
                201
            );
        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $data = $this->schoolYear->show($id);
            if (! $data) {
                return ResponseHelper::notFound('Data tidak ditemukan');
            }

            return ResponseHelper::success(
                new SchoolYearResource($data),
                'Detail tahun ajaran ditemukan'
            );
        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = $this->schoolYear->delete($id);

            if (! $deleted) {
                return ResponseHelper::notFound('Data tidak ditemukan');
            }

            return ResponseHelper::success(null, 'Data tahun ajaran berhasil dihapus');
        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function active()
    {
        try {
            $data = $this->schoolYear->get()->where('active', true)->first();

            if (! $data) {
                return ResponseHelper::notFound('Tidak ada tahun ajaran yang aktif');
            }

            return ResponseHelper::success(
                new SchoolYearResource($data),
                'Tahun ajaran aktif ditemukan'
            );
        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function cronStatus()
    {
        try {
            $latest = \App\Models\SchoolYear::orderByDesc('created_at')->first();

            if (! $latest) {
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
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil status cron job',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
