<?php

namespace App\Contracts\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface StudentLessonScheduleInterface
{
    public function getSchedule(string $studentId, ?string $day = null): Collection;
    public function getAllLessonHoursByDay(?string $day = null): Collection;
    public function getStudentById(string $studentId): mixed;
}