<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\StudentLessonScheduleInterface;
use App\Models\LessonSchedule;
use App\Models\LessonHour;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;

class StudentLessonScheduleRepository implements StudentLessonScheduleInterface
{
    public function getStudentById(string $studentId): Student
    {
        return Student::with('classroomStudents')->findOrFail($studentId);
    }

    public function getAllLessonHoursByDay(?string $day = null): Collection
    {
        return LessonHour::query()
            ->when($day, fn($q) => $q->where('day', $day))
            ->orderBy('start')
            ->get();
    }

    public function getSchedule(string $studentId, ?string $day = null): Collection
    {
        return LessonSchedule::query()
            ->whereHas('classroom.classroomStudents', function ($q) use ($studentId) {
                $q->where('student_id', $studentId)
                  ->where('status', 'ACTIVE'); // kelas aktif
            })
            ->when($day, fn($q) => $q->where('day', $day))
            ->with(['subject', 'employee.user', 'lessonHour'])
            ->orderBy('lesson_hour_id')
            ->get();
    }
}
