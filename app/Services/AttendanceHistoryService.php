<?php

namespace App\Services;

use App\Contracts\Interfaces\AttendanceInterface;

class AttendanceHistoryService
{
    private AttendanceInterface $attendanceRepo;

    public function __construct(AttendanceInterface $attendanceRepo)
    {
        $this->attendanceRepo = $attendanceRepo;
    }

    public function getStudentHistory($studentId)
    {
        return $this->attendanceRepo->getHistoryByStudentId($studentId);
    }
}
