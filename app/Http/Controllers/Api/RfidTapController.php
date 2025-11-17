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
    private $rfidTapService;
    public function __construct(RfidTapService $rfidTapService)
    {
        $this->rfidTapService = $rfidTapService;
    }

    public function tap(TapRfidRequest $request)
    {
        try {
            $result = $this->rfidTapService->processTap($request);

            $statusCode = $result['status'] === 'valid' ? 200 : 400;

            return ResponseHelper::success(
                new TapResultResource($result),
                $result['message'],
                $statusCode
            );

        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }
}