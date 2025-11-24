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
    public function __construct(LessonScheduleInterface $lessonScheduleInterface, AttendanceInterface $attendanceInterface, ClassroomStudentsInterface $classroomStudentsInterface, ClassroomInterface $classroomInterface)
    {
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

    public function getClassroomSchedule(string $classroomId, string $date): \Illuminate\Database\Eloquent\Collection
    {
        try {
            $day = strtolower(Carbon::parse($date)->timezone('Asia/Jakarta')->englishDayOfWeek);
        } catch (\Exception $e) {
            $day = strtolower(now()->timezone('Asia/Jakarta')->englishDayOfWeek);
        }
        
        $schedules = $this->lessonScheduleInterface->getByClassroomAndDay($classroomId, $day);
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

    public function getScheduleWithAttendanceStatus(string $teacherId, string $date): \Illuminate\Database\Eloquent\Collection
    {
        return DB::transaction(function () use ($teacherId, $date) {
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
                if ($schedule->lesson_order === 1) {
                    $classroomAttendance = $this->attendanceInterface->getByClassroomAndDate(
                        $schedule->classroom_id,
                        $date
                    );
                    $rfidAttendance = $classroomAttendance->filter(function ($attendance) use ($schedule) {
                        return $attendance->lesson_order === 1 &&
                               $attendance->attendance_type === 'rfid';
                    });
                    $schedule->attendance_completion_status = $rfidAttendance->isNotEmpty() ? 'completed' : 'pending';
                    $schedule->rfid_attendance_completed = $rfidAttendance->isNotEmpty();
                    $schedule->cross_check_available = false;
                    $schedule->cross_check_completed = false;
                } else {
                    $crossCheckAttendance = $hasCrossCheck->filter(function ($attendance) {
                        return $attendance->attendance_type === 'cross_check';
                    });

                    if ($crossCheckAttendance->isNotEmpty()) {
                        $schedule->attendance_completion_status = 'completed';
                        $schedule->cross_check_completed = true;
                    } else {
                        $classroomSchedules = $this->lessonScheduleInterface->getByClassroomAndDay(
                            $schedule->classroom_id,
                            $day
                        );
                        $firstLessonSchedule = $classroomSchedules->firstWhere('lesson_order', 1);

                        if ($firstLessonSchedule) {
                            $firstLessonAttendance = $this->attendanceInterface->getByClassroomAndDate(
                                $schedule->classroom_id,
                                $date
                            );
                            $rfidCompleted = $firstLessonAttendance->filter(function ($attendance) {
                                return $attendance->lesson_order === 1 &&
                                       $attendance->attendance_type === 'rfid';
                            })->isNotEmpty();

                            $schedule->attendance_completion_status = $rfidCompleted ? 'cross-check-available' : 'pending';
                            $schedule->cross_check_available = $rfidCompleted;
                        } else {
                            $schedule->attendance_completion_status = 'pending';
                            $schedule->cross_check_available = false;
                        }
                        $schedule->cross_check_completed = false;
                    }
                    $schedule->rfid_attendance_completed = false;
                }

                $studentCount = $this->classroomStudentsInterface->getByClassroom($schedule->classroom_id)->count();
                $schedule->student_count = $studentCount;
            }

            return $schedules;
        });
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

    public function validateClassroomAndDate($request): array
    {
        if (!$request->classroom_id) {
            throw new \Exception('ID kelas wajib diisi', 400);
        }

        if (!$request->date) {
            throw new \Exception('Tanggal wajib diisi', 400);
        }

        if (!Carbon::createFromFormat('Y-m-d', $request->date)) {
            throw new \Exception('Format tanggal tidak valid', 400);
        }

        return [
            'classroom_id' => $request->classroom_id,
            'date' => $request->date
        ];
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

    public function getClassroomScheduleWithValidation($request): \Illuminate\Database\Eloquent\Collection
    {
        $validated = $this->validateClassroomAndDate($request);
        return $this->getClassroomSchedule($validated['classroom_id'], $validated['date']);
    }

    public function getScheduleWithAttendanceStatusWithValidation($request, string $teacherId): \Illuminate\Database\Eloquent\Collection
    {
        $date = $this->validateDateRequest($request);
        return $this->getScheduleWithAttendanceStatus($teacherId, $date);
    }
}