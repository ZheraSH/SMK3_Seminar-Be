<?php

namespace App\Http\Controllers\Api;

use App\Services\ClassroomStudentsService;
use App\Http\Resources\ClassroomStudentsResource;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;

class ClassroomStudentsController extends Controller
{
    public function __construct(
        protected ClassroomStudentsService $service
    ) {}

    public function index(Request $request)
    {
        $data = $this->service->search($request);
        return ResponseHelper::success(
            ClassroomStudentsResource::collection($data),
            'Data siswa kelas berhasil diambil'
        );
    }
}
