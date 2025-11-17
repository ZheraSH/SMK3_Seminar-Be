<?php

namespace App\Services;

use App\Contracts\Interfaces\AttendanceInterface;
use App\Models\Attendance;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use Illuminate\Http\Request;

class AttendanceService
{
    public function __construct(private AttendanceInterface $attendance) {}

    public function store(StoreAttendanceRequest $request): Attendance
    {
        $data = $request->validated();
        return $this->attendance->store($data);
    }

    public function update(Attendance $attendance, UpdateAttendanceRequest $request): Attendance
    {
        $data = $request->validated();
        return $this->attendance->update($attendance, $data);
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
    
    public function getStudentMonthlyAttendance(string $studentId, string $month, string $year): mixed
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
}
