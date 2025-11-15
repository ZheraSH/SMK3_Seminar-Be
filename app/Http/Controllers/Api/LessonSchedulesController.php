<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLessonSchedulesRequest;
use App\Http\Requests\UpdateLessonSchedulesRequest;
use App\Http\Resources\LessonScheduleResource;
use App\Http\Resources\ClassroomScheduleResource;
use App\Http\Resources\ClassroomDayScheduleResource;
use App\Services\LessonScheduleService;
use App\Helpers\ResponseHelper;

class LessonSchedulesController extends Controller
{
    private LessonScheduleService $lessonScheduleService;

    public function __construct(LessonScheduleService $lessonScheduleService)
    {
        $this->lessonScheduleService = $lessonScheduleService;
    }

    public function index()
    {
        try {
            $data = $this->lessonScheduleService->getAllClassroomsWithSchedules();
            
            return ResponseHelper::success(
                ClassroomScheduleResource::collection($data),
                'Data jadwal pelajaran semua kelas berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 500, $th->getMessage());
        }
    }

    public function store(StoreLessonSchedulesRequest $request)
    {
        try {
            $data = $this->lessonScheduleService->store($request);
            
            return ResponseHelper::success(
                new LessonScheduleResource($data),
                'Jadwal pelajaran berhasil disimpan',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 500, $th->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $data = $this->lessonScheduleService->show($id);
            
            return ResponseHelper::success(
                new LessonScheduleResource($data),
                'Detail jadwal pelajaran berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::notFound('Jadwal pelajaran tidak ditemukan');
        }
    }

    public function update(UpdateLessonSchedulesRequest $request, string $id)
    {
        try {
            $data = $this->lessonScheduleService->update($id, $request);
            
            return ResponseHelper::success(
                new LessonScheduleResource($data),
                'Jadwal pelajaran berhasil diperbarui'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 500, $th->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->lessonScheduleService->delete($id);
            
            return ResponseHelper::success(
                null,
                'Jadwal pelajaran berhasil dihapus'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 500, $th->getMessage());
        }
    }

    public function getByClassroom(string $classroomId)
    {
        try {
            $data = $this->lessonScheduleService->getByClassroom($classroomId);
            
            return ResponseHelper::success(
                new ClassroomScheduleResource($data),
                'Data jadwal pelajaran kelas berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 500, $th->getMessage());
        }
    }

    public function getByClassroomAndDay(string $classroomId, string $day)
    {
        try {
            $data = $this->lessonScheduleService->getByClassroomAndDay($classroomId, $day);
            
            return ResponseHelper::success(
                new ClassroomDayScheduleResource($data),
                'Data jadwal pelajaran kelas per hari berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 500, $th->getMessage());
        }
    }
}