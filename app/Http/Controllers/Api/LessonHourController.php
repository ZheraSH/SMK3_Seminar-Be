<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\StoreLessonHourRequest;
use App\Http\Requests\Operator\UpdateLessonHourRequest;
use App\Http\Resources\Operator\LessonHourResource;
use App\Services\Operator\LessonHourService;

class LessonHourController extends Controller
{
    private LessonHourService $lessonHourService;

    public function __construct(LessonHourService $lessonHourService)
    {
        $this->lessonHourService = $lessonHourService;
    }

    public function store(StoreLessonHourRequest $request)
    {
        try {
            $data = $this->lessonHourService->store($request);

            return ResponseHelper::success(
                new LessonHourResource($data),
                'Data jam pelajaran berhasil dibuat',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function update(UpdateLessonHourRequest $request, string $id)
    {
        try {
            $data = $this->lessonHourService->update($id, $request);

            return ResponseHelper::success(
                new LessonHourResource($data),
                'Data jam pelajaran berhasil diperbarui'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->lessonHourService->delete($id);

            return ResponseHelper::success(
                null,
                'Data jam pelajaran berhasil dihapus'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function getByDay(string $day)
    {
        try {
            $data = $this->lessonHourService->getByDay($day);

            return ResponseHelper::success(
                LessonHourResource::collection($data),
                'Data jam pelajaran untuk hari ' . $day . ' berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::notFound('Detail data jam pelajaran tidak ditemukan');
        }
    }
}