<?php

namespace App\Services\Teacher;

use App\Contracts\Repositories\AttendanceRepository;
use App\Contracts\Repositories\Operator\LessonScheduleRepository;
use App\Contracts\Repositories\Operator\ClassroomStudentsRepository;
use App\Enums\AttendanceProofEnum;
use App\Enums\AttendanceStatusEnum;
use App\Enums\DayEnum;
use App\Enums\StudentStatusEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TeacherService
{
    private LessonScheduleRepository $lessonScheduleRepository;
    private ClassroomStudentsRepository $classroomStudentsRepository;
    private AttendanceRepository $attendanceRepository;

    public function __construct(LessonScheduleRepository $lessonScheduleRepository, AttendanceRepository $attendanceRepository, ClassroomStudentsRepository $classroomStudentsRepository)
    {
        $this->lessonScheduleRepository = $lessonScheduleRepository;
        $this->classroomStudentsRepository = $classroomStudentsRepository;
        $this->attendanceRepository = $attendanceRepository;
    }

    /* =====================================================
     |  SCHEDULE SECTION
     ===================================================== */

    public function getTodaySchedule(User $user, Request $request): Collection
    {
        $teacherId = $user->employee->id;
        $date = now()->format('Y-m-d');
        $dayId = $this->getIndonesianDayFromDate($date);
        $day = DayEnum::translate($dayId);

        $schedules = $this->lessonScheduleRepository->getByTeacherAndDayWithLessonHour($teacherId, $day);

        foreach ($schedules as $schedule) {
            $hasCrossCheck = $this->attendanceRepository
                ->getByScheduleAndDate($schedule->id, $date)
                ->where('attendance_type', 'cross_check')
                ->isNotEmpty();

            $schedule->has_cross_checked = $hasCrossCheck;
            $schedule->can_cross_check = $schedule->lessonHour->order >= 1;
        }

        return $schedules;
    }

    public function getScheduleByDay(User $user, Request $request, string $day): Collection
    {
        $teacherId = $user->employee->id;
        $validDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $day = strtolower($day);

        if (!in_array($day, $validDays)) {
            throw new \Exception('Invalid day. Use: monday, tuesday, wednesday, thursday, or friday', 400);
        }

        $date = $this->getDateFromDayName($day);

        $dayId = $this->getIndonesianDayFromDate($date);
        $dayCode = DayEnum::translate($dayId);

        $schedules = $this->lessonScheduleRepository->getByTeacherAndDayWithLessonHour($teacherId, $dayCode);

        foreach ($schedules as $schedule) {
            $hasCrossCheck = $this->attendanceRepository
                ->getByScheduleAndDate($schedule->id, $date)
                ->where('attendance_type', 'cross_check')
                ->isNotEmpty();

            $schedule->has_cross_checked = $hasCrossCheck;
            $schedule->can_cross_check = $schedule->lessonHour->order >= 1;
        }

        return $schedules;
    }

    /* =====================================================
     |  ATTENDANCE CROSS-CHECK SECTION
     ===================================================== */

    public function getTeacherClassroomsByDay(string $teacherId, string $day): Collection
    {
        $schedules = $this->lessonScheduleRepository->getByTeacherAndDayWithLessonHour($teacherId, $day);

        $schedules->load([
            'classroom.major',
            'classroom.levelClass',
            'classroom.homeroomTeacher.user',
            'classroom.schoolYear',
            'classroom.classroomStudents' => fn($q) => $q->where('status', StudentStatusEnum::ACTIVE->value),
            'lessonHour'
        ]);

        $date = $this->getDateFromDayName($day);

        return $schedules
            ->groupBy('classroom_id')
            ->map(function ($group) use ($date) {
                $sorted = $group->sortBy('lessonHour.order');
                $first = $sorted->first();
                $last = $sorted->last();

                $orderDisplay = $first->lessonHour->order;
                if ($first->lessonHour->id !== $last->lessonHour->id) {
                    $orderDisplay = "{$first->lessonHour->order} - {$last->lessonHour->order}";
                }

                $hasCrossCheck = $this->attendanceRepository
                    ->getByScheduleAndDate($last->id, $date)
                    ->where('attendance_type', 'cross_check')
                    ->isNotEmpty();

                $last->has_cross_checked = $hasCrossCheck;
                $last->can_cross_check = $last->lessonHour?->order >= 1;
                $last->lesson_order_display = $orderDisplay;

                return (object) [
                    'classroom' => $last->classroom,
                    'first_schedule' => $last,
                    'date' => $date
                ];
            })
            ->values();
    }

    public function getTodayClassrooms(User $user, Request $request): Collection
    {
        $teacherId = $user->employee->id;
        $today = now()->locale('id');
        $dayName = strtolower($today->dayName);

        return $this->getTeacherClassroomsByDay($teacherId, $dayName);
    }

    public function getClassroomsByDay(User $user, Request $request, string $day): Collection
    {
        $teacherId = $user->employee->id;
        $validDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $day = strtolower($day);

        if (!in_array($day, $validDays)) {
            throw new \Exception('Invalid day. Use: monday, tuesday, wednesday, thursday, or friday', 400);
        }

        return $this->getTeacherClassroomsByDay($teacherId, $day);
    }

    public function getCrossCheckData(User $user, Request $request): object
    {
        $teacherId = $user->employee->id;
        $classroomId = $request->input('classroom_id');
        $date = $request->input('date');
        $lessonOrder = $request->input('lesson_order');

        $dayId = $this->getIndonesianDayFromDate($date);
        $day = DayEnum::translate($dayId);

        $schedule = $this->lessonScheduleRepository->getByTeacherClassroomAndLessonOrder(
            $teacherId,
            $classroomId,
            $day,
            $lessonOrder
        );

        if (!$schedule) {
            throw new \Exception('Jadwal mengajar tidak ditemukan', 404);
        }

        $students = $this->classroomStudentsRepository->getByClassroomForAttendance((string)$classroomId, $request);

        $students->getCollection()->transform(function ($classroomStudent) use ($date, $lessonOrder) {
            $student = $classroomStudent->student;
            $existingAttendance = $this->attendanceRepository->getByStudentLesson($student->id, $date, $lessonOrder);
            $rfidAttendance = $this->attendanceRepository->getRFIDAttendanceByStudentAndDate($student->id, $date);
            $currentStatus = $existingAttendance?->status?->value ?? null;
            $isLocked = $existingAttendance?->is_locked ?? false;

            return [
                'student_id' => $student->id,
                'name' => $student->user->name,
                'nisn' => $student->nisn,
                'existing_attendance' => $existingAttendance,
                'rfid_info' => $rfidAttendance ? [
                    'checkin_time' => $rfidAttendance->checkin_time,
                    'checkout_time' => $rfidAttendance->checkout_time,
                    'status' => $rfidAttendance->status
                ] : null,
                'is_locked' => $isLocked,
                'current_status' => $currentStatus
            ];
        });

        $submissionRecord = $this->attendanceRepository->getSubmissionInfo($schedule->id, $date, $lessonOrder);
        $hasSubmitted = (bool) $submissionRecord;
        $submittedAt = $submissionRecord?->updated_at;

        $canResubmit = true;

        if ($hasSubmitted) {
            $now = Carbon::now();
            $startTime = Carbon::parse($date . ' ' . $schedule->lessonHour->start);
            $endTime = Carbon::parse($date . ' ' . $schedule->lessonHour->end);
            $canResubmit = $now->format('Y-m-d') === $date && $now->between($startTime, $endTime);
        }

        return (object) [
            'lesson_schedule' => $schedule,
            'classroom' => $schedule->classroom,
            'students' => $students,
            'summary' => $this->getClassroomSummary($classroomId, $date),
            'date' => $date,
            'lesson_order' => $lessonOrder,
            'classroom_id' => $classroomId,
            'submission_status' => (object) [
                'has_submitted' => $hasSubmitted,
                'submitted_at' => $submittedAt,
                'can_resubmit' => $canResubmit
            ]
        ];
    }

    public function submitCrossCheck(User $user, Request $request): array
    {
        $teacherId = $user->employee->id;
        $data = $request->validated();

        $date = $data['date'];
        $classroomId = $data['classroom_id'];

        $dayId = $this->getIndonesianDayFromDate($date);
        $day = DayEnum::translate($dayId);

        $allSchedules = $this->lessonScheduleRepository->getByTeacherAndDayWithLessonHour($teacherId, $day)
            ->where('classroom_id', $classroomId);

        return DB::transaction(function () use ($data, $teacherId, $allSchedules, $date) {
            $results = [];

            foreach ($data['attendances'] as $attendanceData) {
                $studentId = $attendanceData['student_id'];
                $status = $attendanceData['status'];

                if (!$studentId || $studentId === 'NaN' || $studentId === 'null') {
                    continue;
                }

                $classroomStudent = $this->classroomStudentsRepository->getByStudentAndClassroom(
                    $studentId,
                    $data['classroom_id']
                );

                if (!$classroomStudent) continue;

                foreach ($allSchedules as $schedule) {
                    $lessonOrder = $schedule->lessonHour->order;

                    $isLocked = $this->attendanceRepository->isAttendanceLocked(
                        $studentId,
                        $date,
                        $lessonOrder
                    );

                    if ($isLocked) continue;

                    $attendance = $this->attendanceRepository->updateOrCreate(
                        [
                            'student_id'   => $studentId,
                            'date'         => $date,
                            'lesson_order' => $lessonOrder
                        ],
                        [
                            'classroom_student_id' => $classroomStudent->id,
                            'teacher_id'           => $teacherId,
                            'lesson_schedule_id'   => $schedule->id,
                            'subject_id'           => $schedule->subject_id,
                            'attendance_type'      => 'cross_check',
                            'status'               => $status,
                            'proof'                => AttendanceProofEnum::CLASSROOM->value,
                            'is_final'             => true,
                            'updated_at'           => now()
                        ]
                    );
                    $results[] = $attendance;
                }
            }

            return $results;
        });
    }

    /* =====================================================
     |  PRIVATE HELPERS
     ===================================================== */

    private function getIndonesianDayFromDate(string $date): string
    {
        $carbonDate = Carbon::parse($date)->locale('id');
        return strtolower($carbonDate->dayName);
    }

    private function getDateFromDayName(string $dayName): string
    {
        $dayMap = [
            'monday' => Carbon::MONDAY,
            'tuesday' => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'thursday' => Carbon::THURSDAY,
            'friday' => Carbon::FRIDAY,
        ];

        $targetDay = $dayMap[$dayName];
        $today = Carbon::now();

        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        $targetDate = $startOfWeek->copy()->addDays($targetDay - 1);

        return $targetDate->format('Y-m-d');
    }

    public function validateDate(?string $date, bool $required = true): string
    {
        if ($required && !$date) {
            throw new \Exception('Tanggal wajib diisi', 400);
        }

        $date = $date ?? now()->format('Y-m-d');

        if (!Carbon::hasFormat($date, 'Y-m-d')) {
            throw new \Exception('Format tanggal tidak valid. Gunakan format Y-m-d', 400);
        }

        return $date;
    }

    private function getClassroomSummary(string $classroomId, string $date): array
    {
        $attendances = $this->attendanceRepository->getByClassroomAndDate($classroomId, $date);

        return [
            'total_students' => $this->classroomStudentsRepository->countActiveByClassroom($classroomId),
            'hadir' => $attendances->where('status', AttendanceStatusEnum::PRESENT->value)->count(),
            'terlambat' => $attendances->where('status', AttendanceStatusEnum::LATE->value)->count(),
            'izin' => $attendances->where('status', AttendanceStatusEnum::LEAVE->value)->count(),
            'sakit' => $attendances->where('status', AttendanceStatusEnum::SICK->value)->count(),
            'alpha' => $attendances->where('status', AttendanceStatusEnum::ALPHA->value)->count(),
        ];
    }

    public function validateScheduleConflict(string $teacherId, string $classroomId, string $day, string $lessonHourId, ?string $excludeId = null): void
    {
        if ($this->lessonScheduleRepository->checkTeacherConflict($teacherId, $day, $lessonHourId, $excludeId)) {
            throw new \Exception('Guru sudah mengajar di kelas lain pada jam yang sama', 422);
        }

        if ($this->lessonScheduleRepository->checkClassroomConflict($classroomId, $day, $lessonHourId, $excludeId)) {
            throw new \Exception('Kelas sudah digunakan oleh guru lain pada jam yang sama', 422);
        }
    }
}
