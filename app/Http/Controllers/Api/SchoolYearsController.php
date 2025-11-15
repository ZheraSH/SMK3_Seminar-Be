<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
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
    $active = SchoolYear::where('active', true)->first();

    return response()->json([
        'status' => true,
        'message' => $active
            ? 'Cronjob aktif dan tahun ajaran terbaru telah diatur'
            : 'Cronjob aktif tetapi belum mengatur tahun ajaran',
        'data' => $active
    ], 200);
}


public function cronStatus()
{
    $active = SchoolYear::where('active', true)->first();

    return response()->json([
        'status' => true,
        'message' => $active
            ? 'Cronjob berjalan dan tahun ajaran sudah aktif'
            : 'Cronjob berjalan tetapi belum ada tahun ajaran aktif',
        'data' => $active
    ], 200);
}

}
