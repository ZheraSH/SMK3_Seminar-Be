<?php

namespace App\Http\Controllers\Api;

use App\Enums\DayEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\StoreAttendanceRuleRequest;
use App\Http\Requests\Operator\UpdateAttendanceRuleByDayRequest;
use App\Http\Resources\Operator\AttendanceRuleResource;
use App\Services\Operator\AttendanceRuleService;
use App\Helpers\ResponseHelper;

class AttendanceRuleController extends Controller
{
    private AttendanceRuleService $attendanceRuleService;

    public function __construct(AttendanceRuleService $attendanceRuleService)
    {
        $this->attendanceRuleService = $attendanceRuleService;
    }

    public function store(StoreAttendanceRuleRequest $request)
    {
        try {
            $data = $this->attendanceRuleService->store($request);

            return ResponseHelper::success(
                new AttendanceRuleResource($data),
                'Data aturan kehadiran berhasil disimpan',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function updateByDay(UpdateAttendanceRuleByDayRequest $request, string $day)
    {
        try {
            $data = $this->attendanceRuleService->updateOrCreateByDay($request->validated(), $day);
    
            return ResponseHelper::success(
                new AttendanceRuleResource($data),
                'Data aturan kehadiran berhasil diperbarui'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
    

    public function getByDay(string $day)
    {
        try {
            $data = $this->attendanceRuleService->getByDay($day);

            return ResponseHelper::success(
                new AttendanceRuleResource($data),
                'Data aturan kehadiran berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::notFound('Aturan kehadiran untuk hari tersebut tidak ditemukan');
        }
    }
}