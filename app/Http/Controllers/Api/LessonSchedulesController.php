<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\StoreLessonSchedulesRequest;
use App\Http\Requests\Operator\UpdateLessonSchedulesRequest;
use App\Http\Resources\Operator\LessonScheduleResource;
use App\Http\Resources\Operator\LessonScheduleClassroomAndDayResource;
use App\Services\Operator\LessonScheduleService;
use App\Helpers\ResponseHelper;

class LessonSchedulesController extends Controller
{
    private LessonScheduleService $lessonScheduleService;

    public function __construct(LessonScheduleService $lessonScheduleService)
    {
        $this->lessonScheduleService = $lessonScheduleService;
    }

    public function store(StoreLessonSchedulesRequest $request)
    {
        try {
            $data = $this->lessonScheduleService->store($request);

            return ResponseHelper::success(
                new LessonScheduleResource($data),
                'Data Jadwal pelajaran berhasil disimpan',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function update(UpdateLessonSchedulesRequest $request, string $id)
    {
        try {
            $data = $this->lessonScheduleService->update($id, $request);

            return ResponseHelper::success(
                new LessonScheduleResource($data),
                'Data Jadwal pelajaran berhasil diperbarui'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->lessonScheduleService->delete($id);

            return ResponseHelper::success(
                null,
                'Data Jadwal pelajaran berhasil dihapus'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function getLessonScheduleClassroomAndDay(string $classroomId, string $day)
    {
        try {
            $data = $this->lessonScheduleService->getLessonScheduleClassroomAndDay($classroomId, $day);

            return ResponseHelper::success(
                new LessonScheduleClassroomAndDayResource($data),
                'Data jadwal pelajaran kelas per hari berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }   
    }
}