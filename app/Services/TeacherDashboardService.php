<?php

namespace App\Services;

use App\Contracts\Interfaces\LessonScheduleInterface;
use App\Contracts\Interfaces\AttendanceInterface;
use App\Contracts\Interfaces\ClassroomStudentsInterface;
use App\Contracts\Interfaces\ClassroomInterface;
use App\Services\TeacherAttendanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TeacherDashboardService
{
    private LessonScheduleInterface $lessonScheduleInterface;
    private AttendanceInterface $attendanceInterface;
    private ClassroomStudentsInterface $classroomStudentsInterface;
    private ClassroomInterface $classroomInterface;
    private TeacherAttendanceService $teacherAttendanceService;
    
    public function __construct(
        LessonScheduleInterface $lessonScheduleInterface, 
        AttendanceInterface $attendanceInterface, 
        ClassroomStudentsInterface $classroomStudentsInterface, 
        ClassroomInterface $classroomInterface,
        TeacherAttendanceService $teacherAttendanceService
    ) {
        $this->lessonScheduleInterface = $lessonScheduleInterface;
        $this->attendanceInterface = $attendanceInterface;
        $this->classroomStudentsInterface = $classroomStudentsInterface;
        $this->classroomInterface = $classroomInterface;
        $this->teacherAttendanceService = $teacherAttendanceService;
    }

    public function getDashboardOverview(string $teacherId, string $date): array
    {
        return DB::transaction(function () use ($teacherId, $date) {
            try {
                $day = strtolower(Carbon::parse($date)->timezone('Asia/Jakarta')->englishDayOfWeek);
                $currentTime = Carbon::now()->timezone('Asia/Jakarta');
            } catch (\Exception $e) {
                $day = strtolower(now()->timezone('Asia/Jakarta')->englishDayOfWeek);
                $currentTime = Carbon::now()->timezone('Asia/Jakarta');
            }

            $schedules = $this->lessonScheduleInterface->getByTeacherAndDay($teacherId, $day);

            if ($schedules->isEmpty()) {
                return [
                    'today_date' => $date,
                    'day_name' => Carbon::parse($date)->timezone('Asia/Jakarta')->translatedFormat('l'),
                    'total_schedules' => 0,
                    'completed_attendance' => 0,
                    'pending_attendance' => 0,
                    'total_classrooms' => 0,
                    'current_schedule' => null,
                    'next_schedule' => null
                ];
            }

            $totalSchedules = $schedules->count();
            $completedAttendance = 0;
            $pendingAttendance = 0;
            $currentSchedule = null;
            $nextSchedule = null;
            $classroomIds = [];

            foreach ($schedules as $schedule) {
                $classroomIds[] = $schedule->classroom_id;

                $attendanceStatus = $this->getAttendanceStatus($schedule, $date);

                if ($attendanceStatus === 'completed') {
                    $completedAttendance++;
                } else {
                    $pendingAttendance++;
                }

                $scheduleStartTime = Carbon::createFromFormat('H:i', $schedule->lessonHour->start_time);
                $scheduleEndTime = Carbon::createFromFormat('H:i', $schedule->lessonHour->end_time);

                if ($currentTime->between($scheduleStartTime, $scheduleEndTime) && !$currentSchedule) {
                    $currentSchedule = [
                        'classroom_name' => $schedule->classroom->name,
                        'subject_name' => $schedule->subject->name,
                        'start_time' => $schedule->lessonHour->start_time,
                        'end_time' => $schedule->lessonHour->end_time,
                        'lesson_order' => $schedule->lesson_order,
                        'attendance_status' => $attendanceStatus
                    ];
                }

                if ($currentTime->lt($scheduleStartTime) && !$nextSchedule) {
                    $nextSchedule = [
                        'classroom_name' => $schedule->classroom->name,
                        'subject_name' => $schedule->subject->name,
                        'start_time' => $schedule->lessonHour->start_time,
                        'end_time' => $schedule->lessonHour->end_time,
                        'lesson_order' => $schedule->lesson_order,
                        'attendance_status' => $attendanceStatus
                    ];
                }
            }

            return [
                'today_date' => $date,
                'day_name' => Carbon::parse($date)->timezone('Asia/Jakarta')->translatedFormat('l'),
                'total_schedules' => $totalSchedules,
                'completed_attendance' => $completedAttendance,
                'pending_attendance' => $pendingAttendance,
                'total_classrooms' => count(array_unique($classroomIds)),
                'current_schedule' => $currentSchedule,
                'next_schedule' => $nextSchedule
            ];
        });
    }

    public function getTodaySchedule(string $teacherId, string $date): \Illuminate\Database\Eloquent\Collection
    {
        return DB::transaction(function () use ($teacherId, $date) {
            return $this->teacherAttendanceService->getScheduleWithAttendanceStatus($teacherId, $date);
        });
    }

    public function getTodayClassroomList(string $teacherId, string $date): array
    {
        return DB::transaction(function () use ($teacherId, $date) {
            try {
                $day = strtolower(Carbon::parse($date)->timezone('Asia/Jakarta')->englishDayOfWeek);
            } catch (\Exception $e) {
                $day = strtolower(now()->timezone('Asia/Jakarta')->englishDayOfWeek);
            }

            $schedules = $this->lessonScheduleInterface->getByTeacherAndDay($teacherId, $day);

            if ($schedules->isEmpty()) {
                return [];
            }

            $classroomSchedules = [];
            foreach ($schedules as $schedule) {
                $classroomId = $schedule->classroom_id;

                if (!isset($classroomSchedules[$classroomId])) {
                    $classroomSchedules[$classroomId] = [
                        'classroom_id' => $classroomId,
                        'classroom_name' => $schedule->classroom->name,
                        'major' => $schedule->classroom->major->code ?? '',
                        'level' => $schedule->classroom->levelClass->name ?? '',
                        'subjects' => [],
                        'total_lessons' => 0,
                        'completed_lessons' => 0
                    ];
                }

                $attendanceStatus = $this->getAttendanceStatus($schedule, $date);
                $classroomSchedules[$classroomId]['subjects'][] = [
                    'name' => $schedule->subject->name,
                    'lesson_order' => $schedule->lesson_order,
                    'time' => $schedule->lessonHour->start_time . '-' . $schedule->lessonHour->end_time,
                    'attendance_status' => $attendanceStatus
                ];

                $classroomSchedules[$classroomId]['total_lessons']++;
                if ($attendanceStatus === 'completed') {
                    $classroomSchedules[$classroomId]['completed_lessons']++;
                }
            }

            foreach ($classroomSchedules as $classroomId => &$classroom) {
                $studentCount = $this->classroomStudentsInterface->countActiveByClassroom($classroomId);
                $classroom['student_count'] = $studentCount;

                $times = array_column($classroom['subjects'], 'time');
                sort($times);
                $classroom['first_lesson_time'] = explode('-', $times[0])[0] ?? '00:00';
                $classroom['last_lesson_time'] = explode('-', end($times))[1] ?? '00:00';
                $classroom['attendance_summary'] = $this->getClassroomAttendanceSummary($classroomId, $date);
            }

            return array_values($classroomSchedules);
        });
    }

    public function getClassroomAttendanceSummary(string $classroomId, string $date): array
    {
        return DB::transaction(function () use ($classroomId, $date) {
            try {
                $day = strtolower(Carbon::parse($date)->timezone('Asia/Jakarta')->englishDayOfWeek);
            } catch (\Exception $e) {
                $day = strtolower(now()->timezone('Asia/Jakarta')->englishDayOfWeek);
            }

            $schedules = $this->lessonScheduleInterface->getByClassroomAndDay($classroomId, $day);

            $rfidCompleted = false;
            $crossCheckCompleted = 0;
            $totalCrossCheckAvailable = 0;

            foreach ($schedules as $schedule) {
                if ($schedule->lesson_order === 1) {
                    $attendanceRecords = $this->attendanceInterface->getByClassroomAndDate($classroomId, $date);
                    $rfidAttendance = $attendanceRecords->filter(function ($attendance) use ($schedule) {
                        return $attendance->lesson_order === 1 && $attendance->attendance_type === 'rfid';
                    });
                    $rfidCompleted = $rfidAttendance->isNotEmpty();
                } else {
                    $totalCrossCheckAvailable++;

                    $attendanceRecords = $this->attendanceInterface->getByScheduleAndDate($schedule->id, $date);
                    $crossCheckAttendance = $attendanceRecords->filter(function ($attendance) {
                        return $attendance->attendance_type === 'cross_check';
                    });

                    if ($crossCheckAttendance->isNotEmpty()) {
                        $crossCheckCompleted++;
                    }
                }
            }

            return [
                'rfid_completed' => $rfidCompleted,
                'cross_check_completed' => $crossCheckCompleted,
                'total_cross_check_available' => $totalCrossCheckAvailable
            ];
        });
    }

    function getAttendanceStatus($schedule, string $date): string
    {
        if ($schedule->lesson_order === 1) {
            $attendanceRecords = $this->attendanceInterface->getByClassroomAndDate($schedule->classroom_id, $date);
            $rfidAttendance = $attendanceRecords->filter(function ($attendance) use ($schedule) {
                return $attendance->lesson_order === 1 && $attendance->attendance_type === 'rfid';
            });

            return $rfidAttendance->isNotEmpty() ? 'completed' : 'pending';
        } else {
            $attendanceRecords = $this->attendanceInterface->getByScheduleAndDate($schedule->id, $date);
            $crossCheckAttendance = $attendanceRecords->filter(function ($attendance) {
                return $attendance->attendance_type === 'cross_check';
            });

            if ($crossCheckAttendance->isNotEmpty()) {
                return 'completed';
            }

            $classroomSchedules = $this->lessonScheduleInterface->getByClassroomAndDay($schedule->classroom_id, strtolower(Carbon::parse($date)->englishDayOfWeek));
            $firstLessonSchedule = $classroomSchedules->firstWhere('lesson_order', 1);

            if ($firstLessonSchedule) {
                $firstLessonAttendance = $this->attendanceInterface->getByClassroomAndDate($schedule->classroom_id, $date);
                $rfidCompleted = $firstLessonAttendance->filter(function ($attendance) {
                    return $attendance->lesson_order === 1 && $attendance->attendance_type === 'rfid';
                })->isNotEmpty();

                return $rfidCompleted ? 'cross-check-available' : 'pending';
            }

            return 'pending';
        }
    }

    public function getClassroomAttendanceSummaryWithValidation(string $teacherId, string $classroomId, string $date): array
    {
        return DB::transaction(function () use ($teacherId, $classroomId, $date) {
            // Validate classroom exists
            $classroom = $this->classroomInterface->show($classroomId);
            if (!$classroom) {
                throw new \Exception('Kelas tidak ditemukan');
            }

            try {
                $day = strtolower(Carbon::parse($date)->timezone('Asia/Jakarta')->englishDayOfWeek);
            } catch (\Exception $e) {
                $day = strtolower(now()->timezone('Asia/Jakarta')->englishDayOfWeek);
            }
            $teacherSchedules = $this->lessonScheduleInterface->getByTeacherAndDay($teacherId, $day);
            $hasClassroomAccess = $teacherSchedules->contains('classroom_id', $classroomId);

            if (!$hasClassroomAccess) {
                throw new \Exception('Anda tidak mengajar kelas ini');
            }

            return $this->getClassroomAttendanceSummary($classroomId, $date);
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

    public function getDashboardOverviewWithValidation($request, string $teacherId): array
    {
        $date = $this->validateDateRequest($request);
        return $this->getDashboardOverview($teacherId, $date);
    }

    public function getTodayScheduleWithValidation($request, string $teacherId): \Illuminate\Database\Eloquent\Collection
    {
        $date = $this->validateDateRequest($request);
        return $this->getTodaySchedule($teacherId, $date);
    }

    public function getTodayClassroomListWithValidation($request, string $teacherId): array
    {
        $date = $this->validateDateRequest($request);
        return $this->getTodayClassroomList($teacherId, $date);
    }

    public function getClassroomAttendanceSummaryWithValidationAndRequest($request, string $teacherId, string $classroomId): array
    {
        $date = $this->validateDateRequest($request);
        return $this->getClassroomAttendanceSummaryWithValidation($teacherId, $classroomId, $date);
    }
}