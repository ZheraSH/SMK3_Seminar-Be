<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Interfaces\SubjectInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Http\Resources\SubjectResource;
use App\Helpers\ResponseHelper;

class SubjectController extends Controller
{
    private SubjectInterface $subject;

    public function __construct(SubjectInterface $subject)
    {
        $this->subject = $subject;
    }
    public function index()
    {
        $data = $this->subject->get();
        return ResponseHelper::success(SubjectResource::collection($data), 'Data mata pelajaran berhasil diambil');
    }

    public function store(StoreSubjectRequest $request)
    {
        $data = $this->subject->store($request->validated());
        return ResponseHelper::success(new SubjectResource($data), 'Mata pelajaran berhasil ditambahkan', 201);
    }

    public function show($id)
    {
        $data = $this->subject->show($id);
        if (!$data) {
        return ResponseHelper::error(null, 'Data tidak ditemukan', 404);
    }

    return ResponseHelper::success(new SubjectResource($data), 'Detail mata pelajaran ditemukan');
    }
    public function update(UpdateSubjectRequest $request, $id)
    {
        $data = $this->subject->update($id, $request->validated());
        return ResponseHelper::success(new SubjectResource($data), 'Data mata pelajaran berhasil diperbarui');
    }


    public function destroy($id)
    {
        $this->subject->delete($id);
        return ResponseHelper::success(null, 'Data mata pelajaran berhasil dihapus');
    }
}