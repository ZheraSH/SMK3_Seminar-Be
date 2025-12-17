<?php

namespace App\Services\Operator;

use App\Contracts\Repositories\Operator\AttendanceRuleRepository;
use App\Http\Requests\Operator\StoreAttendanceRuleRequest;
use App\Http\Requests\Operator\UpdateAttendanceRuleByDayRequest;
use App\Models\AttendanceRule;
use Illuminate\Support\Facades\DB;
use Exception;

class AttendanceRuleService
{
    private AttendanceRuleRepository $attendanceRuleRepository;

    public function __construct(AttendanceRuleRepository $attendanceRuleRepository)
    {
        $this->attendanceRuleRepository = $attendanceRuleRepository;
    }

    public function store(StoreAttendanceRuleRequest $request): AttendanceRule
    {
        return DB::transaction(function () use ($request) {

            $data = $request->validated();

            if (!($data['is_holiday'] ?? false)) {
                $this->validateTimeRanges($data);
            }

            $existing = $this->attendanceRuleRepository->getByDay($data['day']);
            if ($existing) {
                throw new Exception('Aturan kehadiran untuk hari tersebut sudah ada');
            }

            return $this->attendanceRuleRepository->store($data);
        });
    }

    public function updateOrCreateByDay(UpdateAttendanceRuleByDayRequest $request, string $day): AttendanceRule
    {
        return DB::transaction(function () use ($request, $day) {

            $data = $request->validated();

            if (!($data['is_holiday'] ?? false)) {
                $this->validateTimeRanges($data);
            }

            $existing = $this->attendanceRuleRepository->getByDay($day);

            if ($existing) {
                $this->attendanceRuleRepository->update($existing->id, $data);
                return $existing->fresh();
            }

            $data['day'] = $day;
            return $this->attendanceRuleRepository->store($data);
        });
    }

    public function getByDay(string $day): AttendanceRule
    {
        $data = $this->attendanceRuleRepository->getByDay($day);
        return $data;
    }

    private function validateTimeRanges(array $data): void
    {
        if ($data['checkin_end'] <= $data['checkin_start']) {
            throw new Exception('Waktu akhir check-in harus setelah waktu mulai check-in');
        }

        if ($data['checkout_end'] <= $data['checkout_start']) {
            throw new Exception('Waktu akhir check-out harus setelah waktu mulai check-out');
        }

        if ($data['checkout_start'] <= $data['checkin_end']) {
            throw new Exception('Waktu mulai check-out harus setelah waktu akhir check-in');
        }
    }
}