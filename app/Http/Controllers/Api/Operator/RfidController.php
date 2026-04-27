<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\StoreRfidRequest;
use App\Http\Requests\Operator\UpdateRfidRequest;
use App\Http\Resources\Operator\RfidResource;
use App\Http\Resources\Operator\AvailableStudentResource;
use App\Services\Operator\RfidService;
use App\Models\Rfid;
use App\Helpers\ResponseHelper;
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
                'List data RFID berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::notFound('List data RFID gagal diambil');
        }
    }

    public function store(StoreRfidRequest $request)
    {
        try {
            $data = $this->rfidService->store($request);

            return ResponseHelper::success(
                new RfidResource($data),
                'Data RFID berhasil ditambahkan',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function update(UpdateRfidRequest $request, Rfid $rfid)
    {
        try {
            $data = $this->rfidService->update($rfid->id, $request);

            return ResponseHelper::success(
                new RfidResource($data),
                'Data Siswa berhasil ditetapkan ke RFID'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function destroy(Rfid $rfid)
    {
        try {
            $data = $this->rfidService->delete($rfid->id);

            return ResponseHelper::success(
                new RfidResource($data),
                'Data Pengguna RFID berhasil dihapus, nomor RFID tetap tersimpan'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function getAvailableStudents(Request $request)
    {
        try {
            $data = $this->rfidService->getAvailableStudents($request);

            return ResponseHelper::success(
                AvailableStudentResource::collection($data),
                'Data siswa yang tersedia berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::notFound('Data siswa yang tersedia berhasil diambil');
        }
    }
}