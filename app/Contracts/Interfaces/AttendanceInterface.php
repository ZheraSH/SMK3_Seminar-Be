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
    public function getStudentMonthlyAttendance(string $studentId, string $month, string $year): mixed;
    public function getByStudentLesson(string $studentId, string $date, int $lessonOrder): mixed;
    public function getByStudentAndDate(string $studentId, string $date): mixed;
    public function getStudentMonthly(string $studentId, string $month): mixed;
    public function getTodayByStudent(string $studentId): mixed;
    public function getByClassroomAndDate(string $classroomId, string $date): mixed;
    public function getByClassroom(string $classroomId): mixed;
    public function getByDate(string $date): mixed;
    public function getByScheduleAndDate(string $lessonScheduleId, string $date): mixed;
    public function getSummary(string $studentId): array;
    public function getDailyRecap(string $date, array $filters = []): array;
    public function getTopAlphaStudents(string $date, array $filters = [], int $limit = 5);
    public function countByStatusOnDate(string $date, array $filters = []): array;
    public function totalStudents(array $filters = []): int;

}