<?php

namespace App\Contracts\Interfaces;
        
use App\Contracts\Interfaces\Eloquent\DeleteInterface; 
use App\Contracts\Interfaces\Eloquent\GetInterface;
use App\Contracts\Interfaces\Eloquent\ShowInterface; 
use App\Contracts\Interfaces\Eloquent\StoreInterface; 
use App\Contracts\Interfaces\Eloquent\UpdateInterface;
use Illuminate\Database\Eloquent\Collection;

interface LessonScheduleInterface extends GetInterface, StoreInterface, UpdateInterface, ShowInterface, DeleteInterface
{
    public function getByDay(string $day): mixed;
    public function getFirstLessonByClassroomAndDay(string $classroomId, string $day): mixed;
    public function getByClassroomAndDay(string $classroomId, string $day): mixed;
    public function getByTeacherAndDay(string $teacherId, string $day): mixed;
    public function getByTeacherClassroomAndLessonOrder(string $teacherId, string $classroomId, string $day, int $lessonOrder): mixed;
    public function checkClassroomConflict(string $classroomId, string $day, string $lessonHourId, ?string $excludeId = null): bool;
    public function checkTeacherConflict(string $employeeId, string $day, string $lessonHourId, ?string $excludeId = null): bool;
    public function getByStudentAndDay(string $studentId, string $day): mixed;
    public function getTodayTeacherSchedule(string $teacherId, string $day);
    public function getTodayByTeacher(string $teacherId, string $day): Collection;

}