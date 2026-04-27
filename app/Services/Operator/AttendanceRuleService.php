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
    public function index()
    {
        return $this->attendanceRuleRepository->get();
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

    public function updateByDay(UpdateAttendanceRuleByDayRequest $request, string $day): AttendanceRule
    {
        return DB::transaction(function () use ($request, $day) {
            $data = $request->validated();
            $existing = $this->attendanceRuleRepository->getByDay($day);

            if (!$existing) {
                throw new Exception('Aturan kehadiran untuk hari tersebut tidak ditemukan');
            }

            if ($data['is_holiday'] ?? false) {
                $data['checkin_start'] = null;
                $data['checkin_end'] = null;
                $data['checkout_start'] = null;
                $data['checkout_end'] = null;
            } else {

                $this->validateTimeRanges($data);

                if (empty($data['checkin_start']) || empty($data['checkin_end']) || 
                    empty($data['checkout_start']) || empty($data['checkout_end'])) {
                    throw new Exception('Semua field waktu harus diisi ketika bukan hari libur');
                }
            }

            $this->attendanceRuleRepository->update($existing->id, $data);
            return $existing->fresh();
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