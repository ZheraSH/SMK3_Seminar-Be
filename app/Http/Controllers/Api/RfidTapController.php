<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use App\Http\Requests\TapRfidRequest;
use App\Http\Resources\TapResultResource;
use App\Services\RfidTapService;
use Illuminate\Http\Request;

class RfidTapController extends Controller
{
    private RfidTapService $rfidTapService;

    public function __construct(RfidTapService $rfidTapService)
    {
        $this->rfidTapService = $rfidTapService;
    }

    public function tap(TapRfidRequest $request)
    {
        try {
            $result = $this->rfidTapService->processTap($request);
            $status = ($result['status'] ?? 'invalid') === \App\Enums\TapStatusEnum::VALID->value ? 200 : 400;
            return ResponseHelper::success(new TapResultResource($result), $result['message'] ?? 'OK', $status);
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage() ?: 'Internal Server Error', $th->getCode() >= 400 ? $th->getCode() : 500);
        }
    }
}