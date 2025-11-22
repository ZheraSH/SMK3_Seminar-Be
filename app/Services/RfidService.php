<?php

namespace App\Services;

use App\Contracts\Interfaces\RfidInterface;
use App\Enums\RfidStatusEnum;
use App\Http\Requests\StoreRfidRequest;
use App\Http\Requests\UpdateRfidRequest;
use App\Models\Rfid;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RfidService
{
    private RfidInterface $rfid;

    public function __construct(RfidInterface $rfid)
    {
        $this->rfid = $rfid;
    }

    public function store(StoreRfidRequest $request): Rfid
    {
        $data = $request->validated();

        if ($this->rfid->getByRfidNumber($data['rfid'])) {
            throw new \Exception('Nomor RFID sudah terdaftar');
        }

        if (isset($data['student_id']) && $this->rfid->getByStudentId($data['student_id'])) {
            throw new \Exception('Siswa sudah memiliki kartu RFID');
        }

        $data['status'] = RfidStatusEnum::ACTIVE->value;
        $data['id'] = (string) Str::uuid();

        $rfid = $this->rfid->store($data);
        return $this->rfid->show($rfid->id);
    }

    public function update(string $id, UpdateRfidRequest $request): Rfid
    {
        $rfid = $this->rfid->show($id);
        $data = $request->validated();

        if (isset($data['rfid'])) {
            $existingRfid = $this->rfid->getByRfidNumber($data['rfid']);
            if ($existingRfid && $existingRfid->id !== $rfid->id) {
                throw new \Exception('Nomor RFID sudah terdaftar');
            }
        }

        if (isset($data['student_id'])) {
            $existingStudentRfid = $this->rfid->getByStudentId($data['student_id']);
            if ($existingStudentRfid && $existingStudentRfid->id !== $rfid->id) {
                throw new \Exception('Siswa sudah memiliki kartu RFID');
            }
        }

        $this->rfid->update($rfid->id, $data);
        return $this->rfid->show($rfid->id);
    }

    public function show(string $id): Rfid
    {
        return $this->rfid->show($id);
    }

    public function delete(string $id): bool
    {
        return $this->rfid->delete($id);
    }

    public function getWithFilter(Request $request): LengthAwarePaginator
    {
        return $this->rfid->search($request);
    }

    public function getAvailableStudents(Request $request): Collection
    {
        return $this->rfid->getAvailableStudents($request);
    }

    public function getUsedRfids(): Collection
    {
        return $this->rfid->used();
    }

    public function getNotUsedRfids(): Collection
    {
        return $this->rfid->notUsed();
    }
}