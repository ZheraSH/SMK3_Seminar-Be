<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use App\Http\Requests\TapRfidRequest;
use App\Http\Resources\TapResultResource;
use App\Services\RfidTapService;

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

            return ResponseHelper::success(
                new TapResultResource($result), $result['message'],200);
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}