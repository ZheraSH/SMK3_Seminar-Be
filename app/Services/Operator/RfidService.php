<?php

namespace App\Services\Operator;

use App\Contracts\Repositories\Operator\RfidRepository;
use App\Http\Requests\Operator\StoreRfidRequest;
use App\Http\Requests\Operator\UpdateRfidRequest;
use App\Models\Rfid;
use App\Enums\RfidStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class RfidService
{
    private RfidRepository $rfidRepository;

    public function __construct(RfidRepository $rfidRepository)
    {
        $this->rfidRepository = $rfidRepository;
    }

    public function store(StoreRfidRequest $request): Rfid
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();

            if ($this->rfidRepository->getByRfidNumber($data['rfid'])) {
                throw new \Exception('Nomor RFID sudah terdaftar');
            }

            if ($this->rfidRepository->getByStudentId($data['student_id'])) {
                throw new \Exception('Siswa sudah memiliki kartu RFID');
            }

            $data['id'] = (string) Str::uuid();
            $data['status'] = RfidStatusEnum::ACTIVE->value;

            $rfid = $this->rfidRepository->store($data);

            return $this->rfidRepository->show($rfid->id);
        });
    }

    public function update(string $id, UpdateRfidRequest $request): Rfid
    {
        $rfid = $this->rfidRepository->show($id);
        $data = $request->validated();

        if (!isset($data['status'])) {
            throw new \Exception('Status RFID wajib diisi');
        }

        if (!in_array($data['status'], RfidStatusEnum::values())) {
            throw new \Exception('Status RFID tidak valid');
        }

        $this->rfidRepository->update($rfid->id, [
            'status' => $data['status'],
        ]);

        return $this->rfidRepository->show($rfid->id);
    }

    public function delete(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $rfid = $this->rfidRepository->show($id);
            return $this->rfidRepository->delete($rfid->id);
        });
    }

    public function getWithFilter(Request $request): LengthAwarePaginator
    {
        return $this->rfidRepository->search($request);
    }

    public function getAvailableStudents(Request $request)
    {
        return $this->rfidRepository->getAvailableStudents(
            $request->search,
            $request->limit ?? 10
        );
    }
}