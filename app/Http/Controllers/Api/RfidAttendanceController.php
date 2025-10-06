<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\RfidTapRequest;
use App\Services\Contracts\RfidAttendanceServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RfidAttendanceController extends Controller
{
    public function __construct(private RfidAttendanceServiceInterface $rfidService) {}

    public function tap(RfidTapRequest $request)
    {
        try {
            $attendance = $this->rfidService->tap($request->string('rfid'), $request->input('timestamp'));
            return ResponseHelper::success($attendance, 'RFID attendance recorded');
        } catch (ModelNotFoundException $e) {
            return ResponseHelper::error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            return ResponseHelper::error('Failed to process RFID tap', 400);
        }
    }
}


