<?php

namespace App\Services\Student;

use App\Contracts\Repositories\AttendanceRfidRepository;
use App\Contracts\Repositories\Operator\LessonScheduleRepository;
use App\Contracts\Repositories\Operator\StudentRepository;
use App\Enums\DayEnum;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StudentService
{
    private StudentRepository $studentRepository;
    private AttendanceRfidRepository $attendanceRfidRepository;
    private LessonScheduleRepository $lessonScheduleRepository;

    public function __construct(StudentRepository $studentRepository, AttendanceRfidRepository $attendanceRfidRepository, LessonScheduleRepository $lessonScheduleRepository)
    {
        $this->studentRepository = $studentRepository;
        $this->attendanceRfidRepository = $attendanceRfidRepository;
        $this->lessonScheduleRepository = $lessonScheduleRepository;
    }

    public function getClassroomInfo(User $user, Request $request): array
    {
        $studentId = $user->student->id;
        return $this->studentRepository->getClassroomInfo($studentId, 12);
    }

    public function getStudentHistory(User $user, Request $request)
    {
        $studentId = $user->student->id;
        return $this->attendanceRfidRepository->getStudentHistory($studentId, 10);
    }

    public function getStudentScheduleByDay(User $user, Request $request, ?string $day = null): array
    {
        $studentId = $user->student->id;
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
