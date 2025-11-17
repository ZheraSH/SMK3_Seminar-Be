<?php

namespace App\Http\Controllers\Api;

use App\Enums\DayEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceRuleRequest;
use App\Http\Requests\UpdateAttendanceRuleByDayRequest;
use App\Http\Resources\AttendanceRuleResource;
use App\Services\AttendanceRuleService;
use App\Helpers\ResponseHelper;

class AttendanceRuleController extends Controller
{
    private AttendanceRuleService $attendanceRuleService;

    public function __construct(AttendanceRuleService $attendanceRuleService)
    {
        $this->attendanceRuleService = $attendanceRuleService;
    }

    public function index()
    {
        try {
            $rules = $this->attendanceRuleService->get();

            return ResponseHelper::success(
                AttendanceRuleResource::collection($rules),
                'List data aturan kehadiran berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage() ?: 'Internal Server Error',$th->getCode() >= 400 ? $th->getCode() : 500);
        }
    }

    public function store(StoreAttendanceRuleRequest $request)
    {
        try {
            $rule = $this->attendanceRuleService->store($request);

            return ResponseHelper::success(
                new AttendanceRuleResource($rule),
                'Aturan kehadiran berhasil disimpan',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage() ?: 'Internal Server Error',$th->getCode() >= 400 ? $th->getCode() : 500);
        }
    }

    public function getByDay(string $day)
    {
        try {
            $rule = $this->attendanceRuleService->getByDay($day);

            if (!$rule) return ResponseHelper::notFound('Aturan kehadiran untuk hari tersebut tidak ditemukan');

            return ResponseHelper::success(
                new AttendanceRuleResource($rule),
                'Data aturan kehadiran berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage() ?: 'Internal Server Error',$th->getCode() >= 400 ? $th->getCode() : 500);
        }
    }

    public function updateByDay(UpdateAttendanceRuleByDayRequest $request, string $day)
    {
        try {
            $validated = $request->validated();
            $rule = $this->attendanceRuleService->updateOrCreateByDay($validated, $day);

            $dayLabel = collect(DayEnum::cases())->firstWhere('value', $day)?->label() ?? $day;
            $exists = $this->attendanceRuleService->getByDay($day);

            $message = $exists
                ? "Aturan kehadiran hari {$dayLabel} berhasil diperbarui"
                : "Aturan kehadiran hari {$dayLabel} berhasil dibuat";

            return ResponseHelper::success(
                new AttendanceRuleResource($rule),
                $message
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage() ?: 'Internal Server Error',$th->getCode() >= 400 ? $th->getCode() : 500);
        }
    }
}