<?php

namespace App\Services\Teacher;

use App\Contracts\Repositories\AttendanceRepository;
use App\Contracts\Repositories\Operator\LessonScheduleRepository;
use App\Contracts\Repositories\Operator\ClassroomStudentsRepository;
use App\Enums\AttendanceProofEnum;
use App\Enums\AttendanceStatusEnum;
use App\Enums\DayEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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

     public function getDailySchedule(string $teacherId, string $date): Collection
     {
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

    public function getDailyScheduleWithValidation(Request $request, string $teacherId): Collection
    {
        $date = $this->validateDate($request->date, false);
        return $this->getDailySchedule($teacherId, $date);
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
             'classroom.classroomStudents' => fn ($q) => $q->where('status', 'active'),
             'lessonHour'
         ]);
     
         return $schedules
             ->unique('classroom_id')
             ->values()
             ->map(fn ($s) => (object) [
                 'classroom' => $s->classroom,
                 'first_schedule' => $s
             ]);
     }

    // public function getCrossCheckData(string $teacherId, string $classroomId, string $date, int $lessonOrder, ?Request $request = null): object
    // {
    //     $dayId = $this->getIndonesianDayFromDate($date);
    //     $day = DayEnum::translate($dayId);

    //     $schedule = $this->lessonScheduleRepository->getByTeacherClassroomAndLessonOrder(
    //             $teacherId,
    //             $classroomId,
    //             $day,
    //             $lessonOrder
    //         );

    //     if (!$schedule) {
    //         throw new \Exception('Jadwal mengajar tidak ditemukan', 404);
    //     }

    //     $students = $this->classroomStudentsRepository->getByClassroomForAttendance($classroomId, $request);

    //     $students->getCollection()->transform(function ($classroomStudent) use ($date, $lessonOrder) {
    //         $student = $classroomStudent->student;

    //         $existingAttendance = $this->attendanceRepository->getByStudentLesson($student->id, $date, $lessonOrder);

    //         $isLocked = $existingAttendance && $existingAttendance->is_locked;

    //         $rfidAttendance = $this->attendanceRepository->getRFIDAttendanceByStudentAndDate($student->id, $date);
            
    //         return [
    //             'student_id' => $student->id,
    //             'name' => $student->user->name,
    //             'nisn' => $student->nisn,
    //             'existing_attendance' => $existingAttendance,
    //             'rfid_info' => $rfidAttendance ? [
    //                 'checkin_time' => $rfidAttendance->checkin_time,
    //                 'checkout_time' => $rfidAttendance->checkout_time,
    //                 'status' => $rfidAttendance->status
    //             ] : null,
    //             'is_locked' => $isLocked,
    //             'current_status' => $isLocked 
    //                 ? $existingAttendance->status 
    //                 : ($existingAttendance->status ?? AttendanceStatusEnum::ALPHA->value)
    //         ];
    //     });

    //     return (object) [
    //         'lesson_schedule' => $schedule,
    //         'students' => $students,
    //         'summary' => $this->getClassroomSummary($classroomId, $date),
    //         'date' => $date,
    //         'lesson_order' => $lessonOrder,
    //         'classroom_id' => $classroomId
    //     ];
    // }

    // public function submitCrossCheck(array $data, string $teacherId): array
    // {
    //     return DB::transaction(function () use ($data, $teacherId) {
    //         $results = [];
    //         $currentTime = now()->format('H:i:s');

    //         foreach ($data['attendances'] as $attendanceData) {
    //             $studentId = $attendanceData['student_id'];

    //             $isLocked = $this->attendanceRepository->isAttendanceLocked(
    //                 $studentId,
    //                 $data['date'],
    //                 $data['lesson_order']
    //             );

    //             if ($isLocked) {
    //                 continue;
    //             }

    //             $classroomStudent = $this->classroomStudentsRepository->getByStudentAndClassroom($studentId, $data['classroom_id']);

    //             $attendance = $this->attendanceRepository->updateOrCreate(
    //                 [
    //                     'student_id' => $studentId,
    //                     'date' => $data['date'],
    //                     'lesson_order' => $data['lesson_order']
    //                 ],
    //                 [
    //                     'classroom_student_id' => $classroomStudent?->id,
    //                     'teacher_id' => $teacherId,
    //                     'lesson_schedule_id' => $data['lesson_schedule_id'],
    //                     'subject_id' => $data['subject_id'],
    //                     'attendance_type' => 'cross_check',
    //                     'status' => $attendanceData['status'],
    //                     'proof' => AttendanceProofEnum::CLASSROOM->value,
    //                     'is_final' => true,
    //                     'is_locked' => false,
    //                     'updated_at' => now()
    //                 ]
    //             );

    //             $results[] = $attendance;
    //         }

    //         return $results;
    //     });
    // }

    /* =====================================================
     |  PRIVATE HELPERS
     ===================================================== */

    private function getIndonesianDayFromDate(string $date): string
    {
        $carbonDate = Carbon::parse($date)->locale('id');
        return strtolower($carbonDate->dayName);
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

    public function validateScheduleConflict(string $teacherId, string $classroomId, string $day,string $lessonHourId, ?string $excludeId = null): void
    {
        if ($this->lessonScheduleRepository->checkTeacherConflict($teacherId, $day, $lessonHourId, $excludeId)) {
            throw new \Exception('Guru sudah mengajar di kelas lain pada jam yang sama', 422);
        }

        if ($this->lessonScheduleRepository->checkClassroomConflict($classroomId, $day, $lessonHourId, $excludeId)) {
            throw new \Exception('Kelas sudah digunakan oleh guru lain pada jam yang sama', 422);
        }
    }
}