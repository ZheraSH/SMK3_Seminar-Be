<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRfidRequest;
use App\Http\Requests\UpdateRfidRequest;
use App\Http\Resources\RfidResource;
use App\Models\Rfid;
use App\Services\RfidService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RfidController extends Controller
{
    private RfidService $rfidService;

    public function __construct(RfidService $rfidService)
    {
        $this->rfidService = $rfidService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $rfids = $this->rfidService->getWithFilter($request);

            return ResponseHelper::success(
                RfidResource::collection($rfids),
                'Data RFID berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(),$th->getCode() ?: 500);
        }
    }

    public function store(StoreRfidRequest $request): JsonResponse
    {
        try {
            $rfid = $this->rfidService->store($request);

            return ResponseHelper::success(
                new RfidResource($rfid),
                'RFID berhasil ditambahkan',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(),$th->getCode() ?: 400);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $rfid = $this->rfidService->show($id);

            return ResponseHelper::success(
                new RfidResource($rfid),
                'Detail RFID berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(),404);
        }
    }

    public function update(UpdateRfidRequest $request, Rfid $rfid): JsonResponse
    {
        try {
            $updatedRfid = $this->rfidService->update($request, $rfid);

            return ResponseHelper::success(
                new RfidResource($updatedRfid),
                'RFID berhasil diperbarui'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(),$th->getCode() ?: 400);
        }
    }

    public function destroy(Rfid $rfid): JsonResponse
    {
        try {
            $this->rfidService->delete($rfid);

            return ResponseHelper::success(null,'RFID berhasil dihapus');
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(),$th->getCode() ?: 400);
        }
    }
}