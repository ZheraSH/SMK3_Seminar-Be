<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRfidRequest;
use App\Http\Requests\UpdateRfidRequest;
use App\Http\Resources\RfidResource;
use App\Models\Rfid;
use App\Services\RfidService;
use App\Helpers\ResponseHelper;
use App\Http\Resources\AvailableStudentResource;
use Illuminate\Http\Request;

class RfidController extends Controller
{
    private RfidService $rfidService;

    public function __construct(RfidService $rfidService)
    {
        $this->rfidService = $rfidService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->rfidService->getWithFilter($request);
            
            return ResponseHelper::pagination(
                $data, 
                RfidResource::class, 
                'Data RFID berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function store(StoreRfidRequest $request)
    {
        try {
            $data = $this->rfidService->store($request);

            return ResponseHelper::success(
                new RfidResource($data),
                'RFID berhasil ditambahkan',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function show(string $id)
    {
        try {
            $data = $this->rfidService->show($id);

            return ResponseHelper::success(
                new RfidResource($data),
                'Detail RFID berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error('Data RFID tidak ditemukan', 404);
        }
    }

    public function update(UpdateRfidRequest $request, Rfid $rfid)
    {
        try {
            $data = $this->rfidService->update($rfid->id, $request);

            return ResponseHelper::success(
                new RfidResource($data),
                'RFID berhasil diperbarui'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function destroy(Rfid $rfid)
    {
        try {
            $this->rfidService->delete($rfid->id);

            return ResponseHelper::success(
                null,
                'RFID berhasil dihapus'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function availableStudents(Request $request)
    {
        try {
            $data = $this->rfidService->getAvailableStudents($request);

            return ResponseHelper::success(
                AvailableStudentResource::collection($data),
                'Data siswa yang tersedia berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function used()
    {
        try {
            $data = $this->rfidService->getUsedRfids();

            return ResponseHelper::success(
                RfidResource::collection($data),
                'Data RFID terpakai berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function notUsed()
    {
        try {
            $data = $this->rfidService->getNotUsedRfids();

            return ResponseHelper::success(
                RfidResource::collection($data),
                'Data RFID tidak terpakai berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}