<?php
namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLessonHourRequest;
use App\Http\Resources\LessonHourResource;
use App\Services\LessonHourService;
use Illuminate\Http\Request;

class LessonHourController extends Controller
{
    private LessonHourService $lessonHourService;

    public function __construct(LessonHourService $lessonHourService)
    {
        $this->lessonHourService = $lessonHourService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->lessonHourService->getAll($request);

            return ResponseHelper::success(
                LessonHourResource::collection($data),
                'Daftar jam pelajaran berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 500, $th->getMessage());
        }
    }

    public function store(StoreLessonHourRequest $request)
    {
        try {
            $data = $this->lessonHourService->store($request);

            return ResponseHelper::success(
                new LessonHourResource($data),
                'Jam pelajaran berhasil dibuat',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 400, $th->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $data = $this->lessonHourService->show($id);
            
            return ResponseHelper::success(
                new LessonHourResource($data),
                'Detail jam pelajaran berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::notFound('Jam pelajaran tidak ditemukan');
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->lessonHourService->delete($id);
            
            return ResponseHelper::success(
                null,
                'Jam pelajaran berhasil dihapus'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 400, $th->getMessage());
        }
    }

    public function getByDay(string $day)
    {
        try {
            $data = $this->lessonHourService->getByDay($day);

            return ResponseHelper::success(
                LessonHourResource::collection($data),
                'Jam pelajaran untuk hari ' . $day . ' berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 500, $th->getMessage());
        }
    }

    public function getAllGroupedByDay()
    {
        try {
            $data = $this->lessonHourService->getAllGroupedByDay();

            return ResponseHelper::success(
                $data,
                'Data jam pelajaran dikelompokkan per hari berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 500, $th->getMessage());
        }
    }
}