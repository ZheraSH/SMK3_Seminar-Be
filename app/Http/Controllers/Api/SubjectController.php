<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Contracts\Repositories\SubjectRepository;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Http\Resources\SubjectResource;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Throwable;

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
            $subjects = $this->subjectRepository->paginate();
            return ResponseHelper::success(
                SubjectResource::collection($subjects),
                'List mata pelajaran berhasil diambil'
            );
        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function search(Request $request)
    {
        try {
            $subjects = $this->subjectRepository->search($request);
            return ResponseHelper::success(
                SubjectResource::collection($subjects),
                'Hasil pencarian mata pelajaran'
            );
        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function store(StoreSubjectRequest $request)
    {
        try {
            $subject = $this->subjectRepository->store($request->validated());
            return ResponseHelper::success(
                new SubjectResource($subject),
                'Mata pelajaran berhasil ditambahkan',
                201
            );
        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $subject = $this->subjectRepository->show($id);
            return ResponseHelper::success(
                new SubjectResource($subject),
                'Detail mata pelajaran ditemukan'
            );
        } catch (Throwable $th) {
            return ResponseHelper::error(404, 'Data mata pelajaran tidak ditemukan');
        }
    }

    public function update(UpdateSubjectRequest $request, string $id)
    {
        try {
            $this->subjectRepository->update($id, $request->validated());
            $subject = $this->subjectRepository->show($id);
            return ResponseHelper::success(
                new SubjectResource($subject),
                'Data mata pelajaran berhasil diperbarui'
            );
        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->subjectRepository->delete($id);
            return ResponseHelper::success(null, 'Data mata pelajaran berhasil dihapus');
        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }
}
