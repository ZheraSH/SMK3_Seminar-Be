<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\StudentLessonScheduleInterface;
use App\Enums\StudentStatusEnum;
use App\Enums\DayEnum;
use App\Models\LessonSchedule;
use Illuminate\Database\Eloquent\Collection;

class StudentLessonScheduleRepository implements StudentLessonScheduleInterface
{
    public function getSchedule(string $studentId, ?string $day = null): Collection
    {
        $schedules = LessonSchedule::with([
                'subject',
                'employee.user', 
                'classroom',
                'lessonHour'
            ])
            ->whereHas('classroom.classroomStudents', function($query) use ($studentId) {
                $query->where('student_id', $studentId)
                      ->where('status', StudentStatusEnum::ACTIVE->value);
            })
            ->when($day, function($query) use ($day) {
                $query->where('day', $day);
            }, function($query) {
                $query->whereIn('day', [
                    DayEnum::MONDAY->value,
                    DayEnum::TUESDAY->value, 
                    DayEnum::WEDNESDAY->value,
                    DayEnum::THURSDAY->value,
                    DayEnum::FRIDAY->value
                ]);
            })
            ->get();
            
        return $schedules->sortBy(function($schedule) {
            return $schedule->lessonHour->start;
        })->values();
    }
}