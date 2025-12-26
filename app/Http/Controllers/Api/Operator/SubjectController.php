<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Contracts\Repositories\Operator\SubjectRepository;
use App\Http\Requests\Operator\StoreSubjectRequest;
use App\Http\Requests\Operator\UpdateSubjectRequest;
use App\Http\Resources\Operator\SubjectResource;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;

class SubjectController extends Controller
{
    private SubjectRepository $subjectRepository;

    public function __construct(SubjectRepository $subjectRepository)
    {
        $this->subjectRepository = $subjectRepository;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->subjectRepository->search($request);

            return ResponseHelper::pagination(
                $data,
                SubjectResource::class,
                'List data mata pelajaran berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::notFound('List data mata pelajaran gagal diambil');
        }
    }

    public function store(StoreSubjectRequest $request)
    {
        try {
            $data = $this->subjectRepository->storeOrRestore($request->validated());

            return ResponseHelper::success(
                new SubjectResource($data),
                'Data Mata pelajaran berhasil ditambahkan',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $data = $this->subjectRepository->show($id);

            return ResponseHelper::success(
                new SubjectResource($data),
                'Detail data mata pelajaran berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::notFound('detail data mata pelajaran tidak ditemukan');
        }
    }

    public function update(UpdateSubjectRequest $request, string $id)
    {
        try {
            $updated = $this->subjectRepository->update($id, $request->validated());
            $data = $this->subjectRepository->show($id);

            return ResponseHelper::success(
                new SubjectResource($data),
                'Data mata pelajaran berhasil diperbarui'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->subjectRepository->delete($id);

            return ResponseHelper::success(
                null,
                'Data mata pelajaran berhasil dihapus'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }
}
