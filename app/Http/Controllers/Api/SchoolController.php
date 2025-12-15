<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Operator\SchoolService;
use App\Http\Requests\Operator\UpdateSchoolRequest;
use App\Http\Resources\Operator\SchoolResource;
use App\Helpers\ResponseHelper;

class SchoolController extends Controller
{
    private SchoolService $schoolService;

    public function __construct(SchoolService $schoolService)
    {
        $this->schoolService = $schoolService;
    }

    public function index()
    {
        try {
            $data = $this->schoolService->get();

            return ResponseHelper::success(
                SchoolResource::collection($data),
                'Data sekolah berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::notFound('Data Sekolah gagal diambil');
        }
    }

    public function update(UpdateSchoolRequest $request)
    {
        try {
            $school = $this->schoolService->get()->first();

            $data = $this->schoolService->update($school->id, $request->validated());

            return ResponseHelper::success(
                new SchoolResource($data),
                'Data sekolah berhasil diperbarui'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}
