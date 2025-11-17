<?php

namespace App\Contracts\Interfaces;

use App\Contracts\Interfaces\Eloquent\DeleteInterface;
use App\Contracts\Interfaces\Eloquent\GetInterface;
use App\Contracts\Interfaces\Eloquent\PaginateInterface;
use App\Contracts\Interfaces\Eloquent\SearchInterface;
use App\Contracts\Interfaces\Eloquent\ShowInterface;
use App\Contracts\Interfaces\Eloquent\StoreInterface;
use App\Contracts\Interfaces\Eloquent\UpdateInterface;

interface AttendanceInterface extends GetInterface, StoreInterface, UpdateInterface, ShowInterface, DeleteInterface, SearchInterface, PaginateInterface
{
    public function getByStudentAndDate(string $studentId, string $date): mixed;
    public function getTodayByStudent(string $studentId): mixed;
    public function getByClassroomAndDate(string $classroomId, string $date): mixed;
    public function getStudentMonthlyAttendance(string $studentId, string $month, string $year): mixed;
    public function getByDate(string $date): mixed;
}