<?php
namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLessonHourRequest;
use App\Http\Resources\LessonHourResource;
use App\Models\LessonHour;
use App\Services\LessonHourService;
use App\Contracts\Repositories\LessonHourRepository;
use Throwable;

class LessonHourController extends Controller
{
    private LessonHourService $lessonHourService;
    private LessonHourRepository $lessonHourRepository;

    public function __construct(LessonHourService $lessonHourService, LessonHourRepository $lessonHourRepository) 
    {
        $this->lessonHourService = $lessonHourService;
        $this->lessonHourRepository = $lessonHourRepository;
    }

    public function index()
    {
        try {
            $lessonHours = $this->lessonHourRepository->get();

            return ResponseHelper::success(
                LessonHourResource::collection($lessonHours),
                'Daftar jam pelajaran berhasil diambil'
            );
        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function store(StoreLessonHourRequest $request)
    {
        try {
            $lessonHour = $this->lessonHourService->store($request);

            return ResponseHelper::success(
                new LessonHourResource($lessonHour),
                'Jam pelajaran berhasil dibuat',
                201
            );
        } catch (Throwable $th) {
            return ResponseHelper::error(400, $th->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $lessonHour = LessonHour::find($id);
            if (!$lessonHour) {
                return ResponseHelper::notFound('Jam pelajaran tidak ditemukan');
            }
            return ResponseHelper::success(
                new LessonHourResource($lessonHour),
                'Detail jam pelajaran berhasil diambil'
            );
        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $lessonHour = LessonHour::find($id);
            if (!$lessonHour) {
                return ResponseHelper::notFound('Jam pelajaran tidak ditemukan');
            }
            
            $this->lessonHourService->delete($lessonHour);
            return ResponseHelper::success(null, 'Jam pelajaran berhasil dihapus');
        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function getByDay(string $day)
    {
        try {
            $lessonHours = $this->lessonHourRepository->getByDay($day);

            return ResponseHelper::success(
                LessonHourResource::collection($lessonHours),
                'Jam pelajaran untuk hari ' . $day . ' berhasil diambil'
            );
        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function getAllGroupedByDay()
    {
        try {
            $lessonHours = $this->lessonHourService->getAllGroupedByDay();

            return ResponseHelper::success(
                $lessonHours,
                'Data jam pelajaran dikelompokkan per hari berhasil diambil'
            );
        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }
}