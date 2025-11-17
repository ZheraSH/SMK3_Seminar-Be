<?php

namespace App\Services;

use App\Contracts\Interfaces\RfidInterface;
use App\Enums\RfidStatusEnum;
use App\Http\Requests\StoreRfidRequest;
use App\Http\Requests\UpdateRfidRequest;
use App\Models\Rfid;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        if ($this->rfid->getByStudentId($data['student_id'])) {
            throw new \Exception('Siswa sudah memiliki kartu RFID');
        }

        $data['status'] = RfidStatusEnum::ACTIVE->value;
        $data['id'] = (string) Str::uuid();

        return $this->rfid->store($data);
    }

    public function update(UpdateRfidRequest $request, Rfid $rfid): Rfid
    {
        $data = $request->validated();

        // Validasi RFID number uniqueness hanya jika rfid di-update
        if (isset($data['rfid'])) {
            $existingRfid = $this->rfid->getByRfidNumber($data['rfid']);
            if ($existingRfid && $existingRfid->id !== $rfid->id) {
                throw new \Exception('Nomor RFID sudah terdaftar');
            }
        }

        // Validasi student uniqueness hanya jika student_id di-update
        if (isset($data['student_id'])) {
            $existingStudentRfid = $this->rfid->getByStudentId($data['student_id']);
            if ($existingStudentRfid && $existingStudentRfid->id !== $rfid->id) {
                throw new \Exception('Siswa sudah memiliki kartu RFID');
            }
        }

        $this->rfid->update($rfid->id, $data);
        return $this->rfid->show($rfid->id);
    }

    public function delete(Rfid $rfid): bool
    {
        return $this->rfid->delete($rfid->id);
    }

    public function getWithFilter(Request $request): mixed
    {
        return $this->rfid->search($request);
    }

    public function show(string $id): mixed
    {
        return $this->rfid->show($id);
    }

    public function validateRfidForAttendance(string $rfidNumber): ?Rfid
    {
        $rfid = $this->rfid->getByRfidNumber($rfidNumber);
        
        if (!$rfid) {
            return null;
        }

        if ($rfid->status !== RfidStatusEnum::ACTIVE->value || !$rfid->student_id) {
            return null;
        }

        return $rfid;
    }
}