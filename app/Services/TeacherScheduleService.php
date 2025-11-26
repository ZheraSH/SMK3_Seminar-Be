<?php

namespace App\Services;

use App\Contracts\Interfaces\LessonScheduleInterface;
use App\Contracts\Interfaces\AttendanceInterface;
use App\Contracts\Interfaces\ClassroomStudentsInterface;
use App\Contracts\Interfaces\ClassroomInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TeacherScheduleService
{
    private LessonScheduleInterface $lessonScheduleInterface;
    private AttendanceInterface $attendanceInterface;
    private ClassroomStudentsInterface $classroomStudentsInterface;
    private ClassroomInterface $classroomInterface;
    
    public function __construct(
        LessonScheduleInterface $lessonScheduleInterface, 
        AttendanceInterface $attendanceInterface, 
        ClassroomStudentsInterface $classroomStudentsInterface, 
        ClassroomInterface $classroomInterface
    ) {
        $this->lessonScheduleInterface = $lessonScheduleInterface;
        $this->attendanceInterface = $attendanceInterface;
        $this->classroomStudentsInterface = $classroomStudentsInterface;
        $this->classroomInterface = $classroomInterface;
    }

    public function getDailySchedule(string $teacherId, string $date): \Illuminate\Database\Eloquent\Collection
    {
        try {
            $day = strtolower(Carbon::parse($date)->timezone('Asia/Jakarta')->englishDayOfWeek);
        } catch (\Exception $e) {
            $day = strtolower(now()->timezone('Asia/Jakarta')->englishDayOfWeek);
        }
        
        $schedules = $this->lessonScheduleInterface->getByTeacherAndDay($teacherId, $day);

        foreach ($schedules as $schedule) {
            $schedule->can_cross_check = $schedule->lesson_order >= 2;

            $hasCrossCheck = $this->attendanceInterface->getByScheduleAndDate(
                $schedule->id,
                $date
            );
            $schedule->has_cross_checked = $hasCrossCheck->isNotEmpty();
        }
        return $schedules;
    }

    public function validateDateRequest($request): string
    {
        $date = $request->date ?? now()->format('Y-m-d');

        if (!Carbon::createFromFormat('Y-m-d', $date)) {
            throw new \Exception('Format tanggal tidak valid', 400);
        }

        return $date;
    }

    public function validateDateRequired($request): string
    {
        if (!$request->date) {
            throw new \Exception('Tanggal wajib diisi', 400);
        }

        if (!Carbon::createFromFormat('Y-m-d', $request->date)) {
            throw new \Exception('Format tanggal tidak valid', 400);
        }

        return $request->date;
    }

    public function validateTeacherSchedule($teacherId, $day, $lessonHourId, ?string $excludeId = null): void
    {
        if ($this->lessonScheduleInterface->checkTeacherConflict($teacherId, $day, $lessonHourId, $excludeId)) {
            throw new \Exception('Guru sudah mengajar di kelas lain pada jam yang sama', 422);
        }
    }

    public function validateClassroomSchedule($classroomId, $day, $lessonHourId, ?string $excludeId = null): void
    {
        if ($this->lessonScheduleInterface->checkClassroomConflict($classroomId, $day, $lessonHourId, $excludeId)) {
            throw new \Exception('Kelas sudah digunakan oleh guru lain pada jam yang sama', 422);
        }
    }

    public function validateLessonScheduleData($data): void
    {
        $this->validateTeacherSchedule(
            $data['employee_id'],
            $data['day'],
            $data['lesson_hour_id'],
            $data['id'] ?? null
        );

        $this->validateClassroomSchedule(
            $data['classroom_id'],
            $data['day'],
            $data['lesson_hour_id'],
            $data['id'] ?? null
        );
    }

    public function getDailyScheduleWithValidation($request, string $teacherId): \Illuminate\Database\Eloquent\Collection
    {
        $date = $this->validateDateRequired($request);
        return $this->getDailySchedule($teacherId, $date);
    }
}