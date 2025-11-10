<?php
namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\LessonHourRepository;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLessonHourRequest;
use App\Http\Requests\UpdateLessonHourRequest;
use App\Http\Resources\LessonHourResource;
use Illuminate\Http\Request;
use Throwable;

class LessonHourController extends Controller
{
    private LessonHourRepository $lessonHourRepository;

    public function __construct(LessonHourRepository $lessonHourRepository)
    {
        $this->lessonHourRepository = $lessonHourRepository;
    }

    public function index(Request $request)
    {
        try {
            if ($request->has('keyword') && !empty($request->keyword)) {
                $data = $this->lessonHourRepository->search($request);
            } else {
                $data = $this->lessonHourRepository->paginate();
            }

            return ResponseHelper::success(
                LessonHourResource::collection($data)->response()->getData(true),
                'Daftar jam pelajaran berhasil diambil'
            );
        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function store(StoreLessonHourRequest $request)
    {
        try {
            $data = $this->lessonHourRepository->store($request->validated());
            return ResponseHelper::success(
                new LessonHourResource($data),
                'Jam pelajaran berhasil dibuat',
                201
            );
        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $data = $this->lessonHourRepository->show($id);
            return ResponseHelper::success(
                new LessonHourResource($data),
                'Detail jam pelajaran berhasil diambil'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound('Jam pelajaran tidak ditemukan');
        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function update(UpdateLessonHourRequest $request, string $id)
    {
        try {
            $data = $this->lessonHourRepository->update($id, $request->validated());
            if (!$data) {
                return ResponseHelper::notFound('Jam pelajaran tidak ditemukan');
            }

            $updatedData = $this->lessonHourRepository->show($id);
            return ResponseHelper::success(
                new LessonHourResource($updatedData),
                'Jam pelajaran berhasil diperbarui'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound('Jam pelajaran tidak ditemukan');
        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->lessonHourRepository->delete($id);
            return ResponseHelper::success(null, 'Jam pelajaran berhasil dihapus');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::notFound('Jam pelajaran tidak ditemukan');
        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    /**
     * Get last lesson hour for auto calculation
     */
    public function getLastLessonHour()
    {
        try {
            $lastLessonHour = $this->lessonHourRepository->getLastLessonHour();
            return ResponseHelper::success(
                $lastLessonHour ? new LessonHourResource($lastLessonHour) : null,
                'Data jam pelajaran terakhir berhasil diambil'
            );
        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }
}