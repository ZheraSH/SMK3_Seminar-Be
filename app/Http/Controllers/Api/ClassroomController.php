<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassroomRequest;
use App\Http\Requests\UpdateClassroomRequest;
use App\Http\Resources\ClassroomResource;
use App\Http\Resources\ClassroomDetailResource;
use App\Services\ClassroomService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    private ClassroomService $classroomService;

    public function __construct(ClassroomService $classroomService)
    {
        $this->classroomService = $classroomService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->classroomService->getWithFilter($request);
            
            return ResponseHelper::pagination(
                $data, 
                ClassroomResource::class, 
                'List data kelas berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function store(StoreClassroomRequest $request)
    {
        try {
            $data = $this->classroomService->store($request->validated());

            return ResponseHelper::success(
                new ClassroomResource($data),
                'Data kelas berhasil dibuat',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function show(string $id)
    {
        try {
            $data = $this->classroomService->show($id);

            return ResponseHelper::success(
                new ClassroomDetailResource($data),
                'Detail data kelas berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 404);
        }
    }

    public function update(UpdateClassroomRequest $request, string $id)
    {
        try {
            $data = $this->classroomService->update($id, $request->validated());

            return ResponseHelper::success(
                new ClassroomResource($data),
                'Data kelas berhasil diperbarui'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->classroomService->delete($id);

            return ResponseHelper::success(
                null,
                'Data kelas berhasil dihapus'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}