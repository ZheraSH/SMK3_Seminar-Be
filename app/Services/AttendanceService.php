<?php

namespace App\Services;

use App\Contracts\Interfaces\AttendanceInterface;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceService
{
    public function __construct(private AttendanceInterface $attendance) {}

    public function store(StoreAttendanceRequest $request): Attendance
    {
        return $this->attendance->store($request->validated());
    }

    public function update(Attendance $attendance, UpdateAttendanceRequest $request): Attendance
    {
        return $this->attendance->update($attendance, $request->validated());
    }

    public function delete(Attendance $attendance): bool
    {
        return $this->attendance->delete($attendance);
    }

    public function search(Request $request): mixed
    {
        return $this->attendance->search($request);
    }

    public function show(string $id): mixed
    {
        return $this->attendance->show($id);
    }

    public function getStudentMonthlyAttendance(string $studentId, int $month, int $year): mixed
    {
        return $this->attendance->getStudentMonthlyAttendance($studentId, $month, $year);
    }

    public function getTodayByStudent(string $studentId): mixed
    {
        return $this->attendance->getTodayByStudent($studentId);
    }

    public function getByDate(string $date): mixed
    {
        return $this->attendance->getByDate($date);
    }

    public function getByClassroomAndDate(string $classroomId, string $date): mixed
    {
        return $this->attendance->getByClassroomAndDate($classroomId, $date);
    }

    public function validateDate(array $data): string
    {
        if (empty($data['date']) || !strtotime($data['date'])) {
            throw new \InvalidArgumentException('Tanggal tidak valid');
        }
        return $data['date'];
    }

    public function validateMonthYear(array $data): array
    {
        $month = $data['month'] ?? null;
        $year = $data['year'] ?? null;

        if (!is_numeric($month) || $month < 1 || $month > 12) {
            throw new \InvalidArgumentException('Bulan tidak valid');
        }

        if (!is_numeric($year) || $year < 2020) {
            throw new \InvalidArgumentException('Tahun tidak valid');
        }

        return [(int)$month, (int)$year];
    }
}
