<?php

namespace App\Services\Student;

use App\Contracts\Repositories\AttendanceRepository;
use App\Contracts\Repositories\Operator\LessonScheduleRepository;
use App\Contracts\Repositories\Operator\StudentRepository;
use App\Enums\DayEnum;
use Carbon\Carbon;

class StudentService
{
    private StudentRepository $studentRepository;
    private AttendanceRepository $attendanceRepository;
    private LessonScheduleRepository $lessonScheduleRepository;

    public function __construct(StudentRepository $studentRepository, AttendanceRepository $attendanceRepository, LessonScheduleRepository $lessonScheduleRepository)
    {
        $this->studentRepository = $studentRepository;
        $this->attendanceRepository = $attendanceRepository;
        $this->lessonScheduleRepository = $lessonScheduleRepository;
    }

    public function getClassroomInfo(string $studentId): array
    {
        return $this->studentRepository->getClassroomInfo($studentId, 12);
    }

    public function getStudentHistory($studentId)
    {
        return $this->attendanceRepository->getStudentHistory($studentId, 10);
    }

    public function getStudentScheduleByDay(string $studentId, ?string $day = null): array
    {
        $classroom = $this->studentRepository->getStudentActiveClassroom($studentId);

        if (!$day) {
            $day = DayEnum::from(Carbon::now()->format('l'))->value;
        }

        $schedules = $this->lessonScheduleRepository->getLessonScheduleClassroomAndDay($classroom->id, $day);

        return [
            'classroom' => $classroom,
            'day' => $day,
            'schedules' => $schedules
        ];
    }
}