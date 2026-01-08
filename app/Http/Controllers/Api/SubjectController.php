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
            $subjects = $this->subjectRepository->paginate(12);

            return ResponseHelper::pagination(
                $subjects,
                SubjectResource::class,
                'List mata pelajaran berhasil diambil'
            );
        } catch (Throwable $th) {
            return ResponseHelper::error($th->getMessage(), 500);
        }
    }

    public function search(Request $request)
    {
        try {
            $subjects = $this->subjectRepository->search($request, 12);

            return ResponseHelper::pagination(
                $subjects,
                SubjectResource::class,
                'Hasil pencarian mata pelajaran berhasil diambil'
            );
        } catch (Throwable $th) {
            return ResponseHelper::error($th->getMessage(), 500);
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
            return ResponseHelper::error($th->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $subject = $this->subjectRepository->show($id);

            return ResponseHelper::success(
                new SubjectResource($subject),
                'Detail mata pelajaran berhasil diambil'
            );
        } catch (Throwable $th) {
            return ResponseHelper::error('Data mata pelajaran tidak ditemukan', 404);
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
            return ResponseHelper::error($th->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->subjectRepository->delete($id);

            return ResponseHelper::success(null, 'Data mata pelajaran berhasil dihapus');
        } catch (Throwable $th) {
            return ResponseHelper::error('Gagal menghapus data atau data tidak ditemukan', 500);
        }
    }
}