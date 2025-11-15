<?php

namespace App\Http\Controllers\Api;

use App\Services\ClassroomStudentsService;
use App\Http\Resources\ClassroomStudentsResource;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClassroomStudentsController extends Controller
{
    private ClassroomStudentsService $classroomStudentsService;

    public function __construct(ClassroomStudentsService $classroomStudentsService)
    {
        $this->classroomStudentsService = $classroomStudentsService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->classroomStudentsService->search($request);

            if ($request->has('page')) {
                return ResponseHelper::pagination($data, 'Data siswa kelas berhasil diambil');
            }
            
            return ResponseHelper::success(
                ClassroomStudentsResource::collection($data),
                'Data siswa kelas berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 500, $th->getMessage());
        }
    }
}