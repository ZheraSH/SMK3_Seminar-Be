<?php

namespace App\Services;

use App\Contracts\Interfaces\AttendanceRuleInterface;
use App\Http\Requests\StoreAttendanceRuleRequest;
use App\Http\Requests\UpdateAttendanceRuleByDayRequest;
use App\Models\AttendanceRule;
use Illuminate\Support\Facades\DB;
use Exception;

class AttendanceRuleService
{
    private AttendanceRuleInterface $attendanceRuleInterface;

    public function __construct(AttendanceRuleInterface $attendanceRuleInterface)
    {
        $this->attendanceRuleInterface = $attendanceRuleInterface;
    }

    public function get(): mixed
    {
        return $this->attendanceRuleInterface->get();
    }

    public function getByDay(string $day): mixed
    {
        return $this->attendanceRuleInterface->getByDay($day);
    }

    public function store(StoreAttendanceRuleRequest $request): AttendanceRule
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();

            if (!($data['is_holiday'] ?? false)) {
                $this->validateTimeRanges($data);
            }

            $existingRule = $this->attendanceRuleInterface->getByDay($data['day']);
            if ($existingRule) {
                throw new Exception('Aturan untuk hari ' . $data['day'] . ' sudah ada');
            }

            return $this->attendanceRuleInterface->store($data);
        });
    }

    public function updateByDay(UpdateAttendanceRuleByDayRequest $request, string $day): AttendanceRule
    {
        return DB::transaction(function () use ($request, $day) {
            $data = $request->validated();

            $existing = $this->attendanceRuleInterface->getByDay($day);
            if (!$existing) {
                throw new Exception('Aturan kehadiran untuk hari tersebut tidak ditemukan');
            }

            if ($data['is_holiday'] ?? false) {
                $data = array_merge($data, [
                    'checkin_start' => null,
                    'checkin_end' => null,
                    'checkout_start' => null,
                    'checkout_end' => null,
                ]);
            } else {
                $this->validateTimeRanges($data);
            }

            $this->attendanceRuleInterface->update($existing->id, $data);

            return $existing->fresh();
        });
    }

    private function validateTimeRanges(array $data): void
    {
        foreach (['checkin_start', 'checkin_end', 'checkout_start', 'checkout_end'] as $field) {
            if (empty($data[$field])) {
                throw new Exception('Semua field waktu harus diisi ketika bukan hari libur');
            }
        }

        if (strtotime($data['checkin_end']) <= strtotime($data['checkin_start'])) {
            throw new Exception('Waktu akhir check-in harus setelah waktu mulai check-in');
        }

        if (strtotime($data['checkout_end']) <= strtotime($data['checkout_start'])) {
            throw new Exception('Waktu akhir check-out harus setelah waktu mulai check-out');
        }

        if (strtotime($data['checkout_start']) <= strtotime($data['checkin_end'])) {
            throw new Exception('Waktu mulai check-out harus setelah waktu akhir check-in');
        }
    }
}
