<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\StoreClassroomRequest;
use App\Http\Resources\Operator\ClassroomResource;
use App\Services\Operator\ClassroomService;
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
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
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
                new ClassroomResource($data),
                'Detail data kelas berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::notFound('Detail data kelas tidak ditemukan');
        }
    }
}