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
        return DB::transaction(function () use ($id, $request) {
            $rfid = $this->rfidRepository->show($id);
            $data = $request->validated();

            if (!is_null($rfid->student_id)) {
                throw new \Exception('RFID ini sudah memiliki pengguna');
            }
            if ($this->rfidRepository->getByStudentId($data['student_id'])) {
                throw new \Exception('Siswa sudah memiliki kartu RFID');
            }

            return $this->rfidRepository->assignStudent($rfid->id, $data['student_id']);
        });
    }

    public function delete(string $id): Rfid
    {
        return DB::transaction(function () use ($id) {
            $rfid = $this->rfidRepository->show($id);
            return $this->rfidRepository->unassignStudent($rfid->id);
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