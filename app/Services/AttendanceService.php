<?php

namespace App\Services;

use App\Enums\AttendanceEnum;
use App\Helpers\CrudHelper;
use App\Models\Attendance;
use App\Services\Contracts\AttendanceServiceInterface;

class AttendanceService implements AttendanceServiceInterface
{
    public function listAll(): object
    {
        return Attendance::with(['classroomStudent'])->get();
    }

    public function create(array $payload): Attendance
    {
        $data = [
            'classroom_student_id' => $payload['classroom_student_id'],
            'status' => $payload['status'],
            'point' => $payload['point'] ?? 0,
            'proof' => $payload['proof'] ?? null,
        ];

        $attendance = Attendance::create($data);
        return $attendance->load(['classroomStudent']);
    }

    public function findById(int $id): ?Attendance
    {
        return Attendance::with(['classroomStudent'])->find($id);
    }

    public function update(Attendance $attendance, array $payload): Attendance
    {
        $updated = CrudHelper::update($attendance, $payload);
        return $updated->load(['classroomStudent']);
    }

    public function delete(Attendance $attendance): bool
    {
        return CrudHelper::delete($attendance);
    }
}


