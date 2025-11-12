<?php
namespace App\Services;

use App\Contracts\Interfaces\AttendanceRuleInterface;
use App\Http\Requests\StoreAttendanceRuleRequest;
use App\Models\AttendanceRule;

class AttendanceRuleService
{
    private AttendanceRuleInterface $attendanceRule;

    public function __construct(AttendanceRuleInterface $attendanceRule)
    {
        $this->attendanceRule = $attendanceRule;
    }

    public function store(StoreAttendanceRuleRequest $request): AttendanceRule
    {
        $data = $request->validated();

        if (!($data['is_holiday'] ?? false)) {
            $this->validateTimeRanges($data);
        }

        $existingRule = $this->attendanceRule->getByDay($data['day']);
        
        if ($existingRule) {
            throw new \Exception('Aturan untuk hari ' . $data['day'] . ' sudah ada');
        }

        return $this->attendanceRule->store($data);
    }

    public function updateOrCreateByDay(array $data, string $day): AttendanceRule
    {
        if (!($data['is_holiday'] ?? false)) {
            $this->validateTimeRanges($data);
        }

        $existingRule = $this->attendanceRule->getByDay($day);
        
        if ($existingRule) {
            $this->attendanceRule->update($existingRule->id, $data);
            return $existingRule->fresh();
        }

        $data['day'] = $day;
        return $this->attendanceRule->store($data);
    }

    public function delete(AttendanceRule $attendanceRule): bool
    {
        $this->attendanceRule->delete($attendanceRule->id);
        return true;
    }

    public function get(): mixed
    {
        return $this->attendanceRule->get();
    }

    public function getByDay(string $day): mixed
    {
        return $this->attendanceRule->getByDay($day);
    }

    public function show(string $id): mixed
    {
        return $this->attendanceRule->show($id);
    }

    private function validateTimeRanges(array $data): void
    {
        if (empty($data['checkin_start']) || empty($data['checkin_end']) || 
            empty($data['checkout_start']) || empty($data['checkout_end'])) {
            throw new \Exception('Semua field waktu harus diisi ketika bukan hari libur');
        }

        if (strtotime($data['checkin_end']) <= strtotime($data['checkin_start'])) {
            throw new \Exception('Waktu akhir check-in harus setelah waktu mulai check-in');
        }

        if (strtotime($data['checkout_end']) <= strtotime($data['checkout_start'])) {
            throw new \Exception('Waktu akhir check-out harus setelah waktu mulai check-out');
        }

        if (strtotime($data['checkout_start']) <= strtotime($data['checkin_end'])) {
            throw new \Exception('Waktu mulai check-out harus setelah waktu akhir check-in');
        }
    }
}