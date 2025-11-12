<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Contracts\Repositories\AttendanceRuleRepository;
use App\Enums\DayEnum;
use App\Http\Requests\StoreAttendanceRuleRequest;
use App\Http\Requests\UpdateAttendanceRuleByDayRequest;
use App\Http\Resources\AttendanceRuleResource;
use App\Services\AttendanceRuleService;
use Illuminate\Http\JsonResponse;
use App\Helpers\ResponseHelper;
use Throwable;

class AttendanceRuleController extends Controller
{
    private AttendanceRuleService $attendanceRuleService;
    private AttendanceRuleRepository $attendanceRuleRepository;

    public function __construct(AttendanceRuleService $attendanceRuleService, AttendanceRuleRepository $attendanceRuleRepository)
    {
        $this->attendanceRuleService = $attendanceRuleService;
        $this->attendanceRuleRepository = $attendanceRuleRepository;
    }

    public function index(): JsonResponse
    {
        try {
            $rules = $this->attendanceRuleRepository->get();

            return ResponseHelper::success(
                AttendanceRuleResource::collection($rules),
                'List Data aturan kehadiran berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function store(StoreAttendanceRuleRequest $request): JsonResponse
    {
        try {
            $rule = $this->attendanceRuleService->store($request);
            
            return ResponseHelper::success(
                new AttendanceRuleResource($rule),
                'Aturan kehadiran berhasil disimpan',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function getByDay(string $day): JsonResponse
    {
        try {
            $rule = $this->attendanceRuleRepository->getByDay($day);
            
            if (!$rule) {
                return ResponseHelper::notFound('Aturan kehadiran untuk hari tersebut tidak ditemukan');
            }
            
            return ResponseHelper::success(
                new AttendanceRuleResource($rule),
                'Data aturan kehadiran berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function updateByDay(UpdateAttendanceRuleByDayRequest $request, string $day): JsonResponse
    {
        try {
            $data = $request->validated();

            $rule = $this->attendanceRuleService->updateOrCreateByDay($data, $day);

            $dayLabel = collect(DayEnum::cases())->firstWhere('value', $day)?->label() ?? $day;

            $exists = $this->attendanceRuleRepository->getByDay($day);

            $message = $exists
                ? "Aturan kehadiran hari {$dayLabel} berhasil diperbarui"
                : "Aturan kehadiran hari {$dayLabel} berhasil dibuat";

            return ResponseHelper::success(
                new AttendanceRuleResource($rule),
                $message
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }
}