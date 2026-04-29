<?php

namespace App\Http\Controllers\Api\Operator;

use App\Enums\TapStatusEnum;
use App\Http\Controllers\Controller;
use App\Services\Operator\RfidTapService;
use App\Helpers\ResponseHelper;
use App\Http\Requests\Operator\LogStudentTapRequest;
use App\Http\Requests\Operator\TapRfidRequest;
use App\Http\Resources\Operator\LogStudentTapResource;
use App\Http\Resources\Operator\TapResultResource;
use Illuminate\Http\JsonResponse;

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
            $status = ($result['status'] ?? 'invalid') === TapStatusEnum::VALID->value ? 200 : 400;
            return ResponseHelper::success(new TapResultResource($result), $result['message'] ?? 'OK', $status);
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function add(LogStudentTapRequest $request): JsonResponse
    {
        try {
            $result = $this->rfidTapService->processBulkUpload(
                $request->validated('attendances'),
                $request->validated('date')
            );
            return (new LogStudentTapResource($result))->response()->setStatusCode(200);
        } catch (\Throwable $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}
