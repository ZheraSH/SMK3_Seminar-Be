<?php

namespace App\Services;

use App\Contracts\Interfaces\AttendanceInterface;
use App\Contracts\Interfaces\LessonScheduleInterface;
use App\Contracts\Interfaces\ClassroomStudentsInterface;
use App\Enums\AttendanceStatusEnum;
use App\Enums\AttendanceProofEnum;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TeacherAttendanceService
{
    private AttendanceInterface $attendanceInterface;
    private LessonScheduleInterface $lessonScheduleInterface;
    private ClassroomStudentsInterface $classroomStudentsInterface;

    public function __construct(AttendanceInterface $attendanceInterface, LessonScheduleInterface $lessonScheduleInterface, ClassroomStudentsInterface $classroomStudentsInterface)
    {
        $this->attendanceInterface = $attendanceInterface;
        $this->lessonScheduleInterface = $lessonScheduleInterface;
        $this->classroomStudentsInterface = $classroomStudentsInterface;
    }

    public function getTeacherClassrooms(string $teacherId, string $date)
    {
        $day = $this->getDayFromDate($date);
        $schedules = $this->lessonScheduleInterface->getByTeacherAndDay($teacherId, $day);
        $schedules->load([
            'classroom.major',
            'classroom.levelClass', 
            'classroom.teacher.user',
            'classroom.schoolYear',
            'classroom.classroomStudents' => function($query) {
                $query->where('status', 'active');
            },
            'lessonHour'
        ]);
        $classroomData = [];
        foreach ($schedules as $schedule) {
            $classroomId = $schedule->classroom->id;
            if (!isset($classroomData[$classroomId])) {
                $classroomData[$classroomId] = (object)[
                    'classroom' => $schedule->classroom,
                    'first_schedule' => $schedule
                ];
            }
        }
        return collect($classroomData)->values();
    }

    public function getCrossCheckData(string $teacherId, string $classroomId, string $date, int $lessonOrder, Request $request = null)
    {
        if ($lessonOrder < 2) {
            throw new \Exception('Cross-check hanya untuk jam pelajaran ke-2 dan seterusnya');
        }

        $day = $this->getDayFromDate($date);
        $schedule = $this->lessonScheduleInterface->getByTeacherClassroomAndLessonOrder(
            $teacherId, $classroomId, $day, $lessonOrder
        );
        if (!$schedule) {
            throw new \Exception('Anda tidak memiliki jadwal mengajar untuk kelas ini pada jam pelajaran ini');
        }
        $schedule->load([
            'classroom.major',
            'classroom.levelClass',
            'classroom.schoolYear',
            'classroom.teacher.user'
        ]);

        $existingCrossCheck = $this->checkExistingCrossCheck($schedule->id, $date, $lessonOrder);
        $hasSubmitted = $existingCrossCheck['has_submitted'];
        $submittedAt = $existingCrossCheck['submitted_at'];

        $studentsPaginator = $this->classroomStudentsInterface->getByClassroomForAttendance($classroomId, $request);
        $attendanceData = $studentsPaginator->getCollection()->map(function ($classroomStudent) use ($date, $lessonOrder) {
            $student = $classroomStudent->student;
            $existingAttendance = $this->attendanceInterface->getByStudentLesson($student->id, $date, $lessonOrder);

            return [
                'student_id' => $student->id,
                'name' => $student->user->name,
                'nisn' => $student->nisn,
                'existing_attendance' => $existingAttendance ? [
                    'id' => $existingAttendance->id,
                    'status' => $existingAttendance->status,
                    'status_label' => $existingAttendance->status->label(),
                ] : null,
            ];
        });

        $studentsPaginator->setCollection($attendanceData);
        $summary = $this->getClassroomSummary($classroomId, $date);
        $currentTeacher = auth()->user()->employee;
        
        return (object)[
            'lesson_schedule' => $schedule,
            'summary' => $summary,
            'students' => $studentsPaginator,
            'classroom' => $schedule->classroom,
            'current_teacher' => $currentTeacher,
            'date' => $date,
            'lesson_order' => $lessonOrder,
            'total_students' => $summary['total_students'],
            'present' => $summary['present'],
            'late' => $summary['late'],
            'alpha' => $summary['alpha'],
            'leave' => $summary['leave'],
            'sick' => $summary['sick'],
            'has_submitted' => $hasSubmitted,
            'submitted_at' => $submittedAt,
            'can_resubmit' => true,
        ];
    }

    public function submitCrossCheck(array $data, string $teacherId): array
    {
        return DB::transaction(function () use ($data, $teacherId) {
            $day = $this->getDayFromDate($data['date']);
            
            $schedule = $this->lessonScheduleInterface->getByTeacherClassroomAndLessonOrder(
                $teacherId, 
                $data['classroom_id'], 
                $day, 
                $data['lesson_order']
            );

            if (!$schedule) {
                throw new \Exception('Anda tidak memiliki jadwal mengajar untuk kelas ini pada jam pelajaran ini');
            }

            $schedule->load(['lessonHour']);

            if (!$schedule->lessonHour) {
                throw new \Exception('Data jam pelajaran tidak ditemukan untuk jadwal ini');
            }

            $endTime = $schedule->lessonHour->end ?? $schedule->lessonHour->end_time ?? null;
            if (!$endTime) {
                throw new \Exception('Waktu berakhir jam pelajaran tidak ditemukan');
            }

            $existingCrossCheck = $this->checkExistingCrossCheck($schedule->id, $data['date'], $data['lesson_order']);
            $hasExistingSubmission = $existingCrossCheck['has_submitted'];

            $currentTime = Carbon::now('Asia/Jakarta');
            $this->validateSubmissionTime($schedule, $hasExistingSubmission, $currentTime);

            if ($hasExistingSubmission) {
                $data['date'] = $currentTime->format('Y-m-d');
                $day = $this->getDayFromDate($data['date']);
                $this->validateTeacherSchedule($teacherId, $data['classroom_id'], $day, $data['lesson_order']);
            }

            $results = [];
            foreach ($data['attendances'] as $attendanceData) {
                $results[] = $this->processStudentAttendance($teacherId, $attendanceData, $data);
            }

            return $results;
        });
    }

    public function getScheduleWithAttendanceStatus(string $teacherId, string $date)
    {
        $day = $this->getDayFromDate($date);
        $schedules = $this->lessonScheduleInterface->getByTeacherAndDay($teacherId, $day);

        $currentTeacher = auth()->user()->employee;
        foreach ($schedules as $schedule) {
            $schedule->can_cross_check = $schedule->lesson_order >= 2;
            $hasCrossCheck = $this->attendanceInterface->getByScheduleAndDate($schedule->id, $date);
            $schedule->has_cross_checked = $hasCrossCheck->isNotEmpty();
            $schedule->student_count = $this->classroomStudentsInterface->getByClassroom($schedule->classroom_id)->count();
            $schedule->current_teacher = $currentTeacher;
        }
        return $schedules;
    }

    private function getDayFromDate(string $date): string
    {
        return strtolower(Carbon::parse($date)->setTimezone('Asia/Jakarta')->englishDayOfWeek);
    }

    private function validateTeacherSchedule(string $teacherId, string $classroomId, string $day, int $lessonOrder): void
    {
        $schedule = $this->lessonScheduleInterface->getByTeacherClassroomAndLessonOrder(
            $teacherId, $classroomId, $day, $lessonOrder
        );
        if (!$schedule) {
            throw new \Exception('Anda tidak memiliki jadwal mengajar untuk kelas ini pada jam pelajaran ini');
        }
    }

    private function validateSubmissionTime($lessonSchedule, $hasExistingSubmission, $currentTime): void
    {
        $deadlineTime = Carbon::today('Asia/Jakarta')->setTime(16, 0, 0);

        if ($currentTime->greaterThanOrEqualTo($deadlineTime)) {
            throw new \Exception('Tidak dapat melakukan submit atau resubmit setelah pukul 16:00');
        }

        if ($hasExistingSubmission) {
            if (!$lessonSchedule->lessonHour) {
                throw new \Exception('Data jam pelajaran tidak ditemukan');
            }

            $endTime = $lessonSchedule->lessonHour->end ?? $lessonSchedule->lessonHour->end_time ?? null;
            if (!$endTime) {
                throw new \Exception('Waktu berakhir jam pelajaran tidak ditemukan');
            }

            if (!is_string($endTime)) {
                throw new \Exception('Format waktu jam pelajaran tidak valid');
            }

            $lessonEndTime = Carbon::today('Asia/Jakarta')->setTimeFromTimeString($endTime);

            if ($currentTime->greaterThan($lessonEndTime)) {
                throw new \Exception('Tidak dapat melakukan resubmit karena jam pelajaran sudah berakhir');
            }
        }
    }

    private function processStudentAttendance(string $teacherId, array $attendanceData, array $requestData)
    {
        $existing = $this->attendanceInterface->getByStudentLesson(
            $attendanceData['student_id'],
            $requestData['date'],
            $requestData['lesson_order']
        );

        $payload = $this->buildAttendancePayload($teacherId, $attendanceData, $requestData);

        if ($existing) {
            return $this->attendanceInterface->update($existing->id, $payload);
        }

        return $this->attendanceInterface->store($payload);
    }

    private function buildAttendancePayload(string $teacherId, array $attendanceData, array $requestData): array
    {
        $classroomStudentId = $this->classroomStudentsInterface
            ->getByStudentAndClassroom($attendanceData['student_id'], $requestData['classroom_id'])?->id;

        return [
            'student_id' => $attendanceData['student_id'],
            'classroom_student_id' => $classroomStudentId,
            'teacher_id' => $teacherId,
            'subject_id' => $requestData['subject_id'],
            'lesson_schedule_id' => $requestData['lesson_schedule_id'],
            'date' => $requestData['date'],
            'lesson_order' => $requestData['lesson_order'],
            'attendance_type' => 'cross_check',
            'status' => $attendanceData['status'],
            'proof' => AttendanceProofEnum::CLASSROOM->value,
        ];
    }

    private function getClassroomSummary(string $classroomId, string $date): array
    {
        $attendances = $this->attendanceInterface->getByClassroomAndDate($classroomId, $date);
        $totalStudents = $this->classroomStudentsInterface->countActiveByClassroom($classroomId);

        $summary = [
            'total_students' => $totalStudents,
            'present' => 0,
            'late' => 0,
            'alpha' => 0,
            'leave' => 0,
            'sick' => 0,
        ];

        foreach ($attendances as $att) {
            if ($att->lesson_order === 1) {
                $key = match($att->status) {
                    AttendanceStatusEnum::PRESENT->value => 'present',
                    AttendanceStatusEnum::LATE->value => 'late',
                    AttendanceStatusEnum::ALPHA->value => 'alpha',
                    AttendanceStatusEnum::LEAVE->value => 'leave',
                    AttendanceStatusEnum::SICK->value => 'sick',
                    default => null
                };
                if ($key) $summary[$key]++;
            }
        }
        return $summary;
    }

    private function checkExistingCrossCheck(string $scheduleId, string $date, int $lessonOrder): array
    {
        $attendances = $this->attendanceInterface->getByScheduleAndDate($scheduleId, $date);

        $crossCheckAttendances = $attendances->filter(function ($attendance) use ($lessonOrder) {
            return $attendance->lesson_order === $lessonOrder 
                && $attendance->attendance_type === 'cross_check';
        });

        $hasSubmitted = $crossCheckAttendances->isNotEmpty();
        $submittedAt = null;

        if ($hasSubmitted) {
            $latestAttendance = $crossCheckAttendances->sortByDesc('updated_at')->first();
            $submittedAt = $latestAttendance->updated_at 
                ? Carbon::parse($latestAttendance->updated_at)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s')
                : Carbon::parse($latestAttendance->created_at)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s');
        }

        return [
            'has_submitted' => $hasSubmitted,
            'submitted_at' => $submittedAt,
        ];
    }
}