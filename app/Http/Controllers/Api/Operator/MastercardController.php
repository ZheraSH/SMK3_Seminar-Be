<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\CheckMastercardRequest;
use App\Http\Requests\Operator\StoreMastercardRequest;
use App\Http\Requests\Operator\UpdateMastercardRequest;
use App\Http\Resources\Operator\MastercardResource;
use App\Services\Operator\MastercardService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

class MastercardController extends Controller
{
    private MastercardService $mastercardService;

    public function __construct(MastercardService $mastercardService)
    {
        $this->mastercardService = $mastercardService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->mastercardService->getWithFilter($request);
            return ResponseHelper::pagination(
                $data,
                MastercardResource::class,
                'List mastercard berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }

    public function store(StoreMastercardRequest $request)
    {
        try {
            $data = $this->mastercardService->store($request);
            return ResponseHelper::success(
                new MastercardResource($data),
                'Mastercard berhasil ditambahkan',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $data = $this->mastercardService->show($id);
            return ResponseHelper::success(
                new MastercardResource($data),
                'Detail mastercard berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::notFound('Mastercard tidak ditemukan');
        }
    }

    public function update(UpdateMastercardRequest $request, string $id)
    {
        try {
            $data = $this->mastercardService->update($request, $id);
            return ResponseHelper::success(
                new MastercardResource($data),
                'Mastercard berhasil diperbarui'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->mastercardService->delete($id);
            return ResponseHelper::success(null, 'Mastercard berhasil dihapus');
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }

    public function check(CheckMastercardRequest $request)
    {
        try {
            $isValid = $this->mastercardService->checkRfid($request->rfid);

            if ($isValid) {
                return ResponseHelper::success(null, 'Mastercard valid');
            } else {
                return ResponseHelper::error('Mastercard tidak valid atau tidak ditemukan', 400);
            }
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }
}
