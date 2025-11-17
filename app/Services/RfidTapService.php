<?php

namespace App\Services;

use App\Contracts\Interfaces\RfidInterface;
use App\Contracts\Interfaces\StudentInterface;
use App\Contracts\Interfaces\AttendanceRuleInterface;
use App\Contracts\Interfaces\AttendanceInterface;
use App\Contracts\Interfaces\LessonScheduleInterface;
use App\Enums\AttendanceProofEnum;
use App\Enums\AttendanceStatusEnum;
use App\Enums\RfidStatusEnum;
use App\Enums\StudentStatusEnum;
use App\Enums\TapStatusEnum;
use App\Enums\TapTypeEnum;
use App\Helpers\TapHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RfidTapService
{
    public function __construct(
        private RfidInterface $rfid,
        private StudentInterface $student,
        private AttendanceInterface $attendance,
        private AttendanceRuleInterface $attendanceRule,
        private LessonScheduleInterface $lessonSchedule
    ) {}

    public function processTap(Request $request): array
    {
        return DB::transaction(function () use ($request) {
            $rfid = $this->validateRfid($request->rfid);
            $student = $this->validateStudent($rfid);
            $timeValidation = $this->validateTapTime();

            if (!$timeValidation['valid']) {
                return $this->createErrorResponse(
                    $timeValidation['status'],
                    $timeValidation['message'],
                    $student,
                    $rfid
                );
            }

            $rule = $timeValidation['rule'];
            $now = $timeValidation['now'];
            
            return $this->processAttendance($student, $rfid, $rule, $now);
        });
    }

    private function validateRfid(string $rfidNumber)
    {
        $rfid = $this->rfid->getByRfidNumber($rfidNumber);
        
        if (!$rfid) {
            throw new \Exception('Kartu RFID tidak valid', 400);
        }

        $statusValue = $this->getEnumValue($rfid->status, RfidStatusEnum::class);

        if ($statusValue !== RfidStatusEnum::ACTIVE->value) {
            throw new \Exception('Kartu RFID tidak aktif', 400);
        }

        return $rfid;
    }

    private function validateStudent($rfid)
    {
        $student = $rfid->student;
        
        if (!$student) {
            throw new \Exception('Kartu RFID belum terhubung ke siswa', 400);
        }

        // Load relationships yang diperlukan
        $student->load([
            'user',
            'classroomStudents.classroom.major',
            'classroomStudents.classroom.levelClass'
        ]);

        $studentStatusValue = $this->getEnumValue($student->status, StudentStatusEnum::class);

        if ($studentStatusValue !== StudentStatusEnum::ACTIVE->value) {
            throw new \Exception('Siswa tidak aktif', 400);
        }

        $activeClassroom = $student->classroomStudents
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->first();

        if (!$activeClassroom) {
            throw new \Exception('Siswa tidak terdaftar di kelas aktif', 400);
        }

        return $student;
    }

    private function validateTapTime(): array
    {
        $now = Carbon::now();
        $day = strtolower($now->englishDayOfWeek);

        $rule = $this->attendanceRule->getByDay($day);
        
        if (!$rule) {
            return [
                'valid' => false,
                'status' => TapStatusEnum::INVALID,
                'message' => 'Tidak ada aturan absensi untuk hari ini'
            ];
        }

        if ($rule->is_holiday) {
            return [
                'valid' => false,
                'status' => TapStatusEnum::INVALID,
                'message' => 'Hari ini libur, tidak ada absen'
            ];
        }

        $isCheckinTime = TapHelper::isWithinTimeRange($now, $rule->checkin_start, $rule->checkin_end);
        $isCheckoutTime = TapHelper::isWithinTimeRange($now, $rule->checkout_start, $rule->checkout_end);

        if (!$isCheckinTime && !$isCheckoutTime) {
            $message = $now->lessThan(Carbon::parse($rule->checkin_start)) 
                ? 'Belum waktu absen masuk' 
                : 'Sudah lewat waktu absen';

            return [
                'valid' => false,
                'status' => TapStatusEnum::BEFORE_TIME,
                'message' => $message
            ];
        }

        return [
            'valid' => true,
            'rule' => $rule,
            'now' => $now,
            'isCheckinTime' => $isCheckinTime,
            'isCheckoutTime' => $isCheckoutTime
        ];
    }

    private function processAttendance($student, $rfid, $rule, Carbon $now): array
    {
        $todayAttendance = $this->attendance->getTodayByStudent($student->id);

        if ($this->shouldProcessCheckin($todayAttendance)) {
            return $this->processCheckin($student, $rfid, $rule, $now, $todayAttendance);
        } else {
            return $this->processCheckout($student, $rfid, $rule, $now, $todayAttendance);
        }
    }

    private function shouldProcessCheckin($attendance): bool
    {
        return !$attendance || ($attendance && !$attendance->checkout_time);
    }

    private function processCheckin($student, $rfid, $rule, Carbon $now, $todayAttendance): array
    {
        if ($todayAttendance && TapHelper::isDuplicateTap($todayAttendance, $now, TapTypeEnum::CHECKIN->value)) {
            return $this->createDuplicateResponse(
                $student,
                $todayAttendance,
                TapTypeEnum::CHECKIN,
                'Absen masuk sudah tercatat sebelumnya'
            );
        }

        $isFirstLesson = $this->isFirstLessonTime($student, $now);
        
        if (!$isFirstLesson) {
            return $this->createTapRecordResponse(
                $student,
                $rfid,
                $now,
                'Tap RFID berhasil. Absensi akan dilakukan oleh guru pada jam pelajaran selanjutnya.'
            );
        }

        $expectedCheckin = Carbon::parse($rule->checkin_start);
        $attendanceStatus = TapHelper::calculateAttendanceStatus($now, $expectedCheckin);

        $activeClassroomStudent = $student->classroomStudents
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->first();

        $attendanceData = [
            'student_id' => $student->id,
            'classroom_student_id' => $activeClassroomStudent?->id,
            'rfid_id' => $rfid->id,
            'date' => $now->toDateString(),
            'checkin_time' => $now,
            'status' => $attendanceStatus,
            'tap_type' => TapTypeEnum::CHECKIN->value,
            'proof' => AttendanceProofEnum::RFID->value,
        ];

        if ($todayAttendance) {
            $this->attendance->update($todayAttendance->id, $attendanceData);
            $attendance = $todayAttendance->fresh(['student', 'rfid', 'classroomStudent.classroom']);
        } else {
            $attendance = $this->attendance->store($attendanceData);
            $attendance = $this->attendance->show($attendance->id);
        }

        return $this->createSuccessResponse(
            TapStatusEnum::VALID,
            $attendanceStatus === AttendanceStatusEnum::ON_TIME->value 
                ? 'Hadir tepat waktu' 
                : 'Terlambat',
            $student,
            $rfid,
            $attendance,
            TapTypeEnum::CHECKIN,
            $attendanceStatus
        );
    }

    private function isFirstLessonTime($student, Carbon $now): bool
    {
        $activeClassroom = $this->getActiveClassroom($student);

        if (!$activeClassroom) {
            return false;
        }

        $day = strtolower($now->englishDayOfWeek);
        
        $firstLesson = $this->lessonSchedule->getFirstLessonByClassroomAndDay($activeClassroom->id, $day);

        if (!$firstLesson || !$firstLesson->lessonHour) {
            return false;
        }

        $firstLessonStart = Carbon::parse($firstLesson->lessonHour->start);
        $firstLessonEnd = Carbon::parse($firstLesson->lessonHour->end);

        return $now->between($firstLessonStart, $firstLessonEnd);
    }

    private function getActiveClassroom($student)
    {
        $activeClassroomStudent = $student->classroomStudents
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->first();

        return $activeClassroomStudent?->classroom;
    }

    private function createTapRecordResponse($student, $rfid, Carbon $now, string $message): array
    {
        $activeClassroom = $this->getActiveClassroom($student);

        return [
            'status' => TapStatusEnum::VALID->value,
            'message' => $message,
            'student' => $this->formatStudentData($student),
            'rfid' => $this->formatRfidData($rfid),
            'type' => TapTypeEnum::CHECKIN->value,
            'attendance_status' => null,
            'attendance' => null,
            'requires_manual_attendance' => true,
            'timestamp' => $now->toISOString()
        ];
    }

    private function processCheckout($student, $rfid, $rule, Carbon $now, $todayAttendance): array
    {
        if (!$todayAttendance) {
            throw new \Exception('Belum melakukan absen masuk');
        }

        if ($todayAttendance->checkout_time) {
            return $this->createDuplicateResponse(
                $student,
                $todayAttendance,
                TapTypeEnum::CHECKOUT,
                'Absen pulang sudah tercatat sebelumnya'
            );
        }

        $this->attendance->update($todayAttendance->id, [
            'checkout_time' => $now,
            'tap_type' => TapTypeEnum::CHECKOUT->value,
        ]);

        $attendance = $todayAttendance->fresh();

        return $this->createSuccessResponse(
            TapStatusEnum::VALID,
            'Absen pulang berhasil',
            $student,
            $rfid,
            $attendance,
            TapTypeEnum::CHECKOUT
        );
    }

    private function createSuccessResponse(
        TapStatusEnum $status,
        string $message,
        $student,
        $rfid,
        $attendance,
        TapTypeEnum $type,
        ?string $attendanceStatus = null
    ): array {
        return [
            'status' => $status->value,
            'message' => $message,
            'student' => $this->formatStudentData($student),
            'rfid' => $this->formatRfidData($rfid),
            'attendance' => $attendance,
            'type' => $type->value,
            'attendance_status' => $attendanceStatus,
            'timestamp' => now()->toISOString()
        ];
    }

    private function createErrorResponse(TapStatusEnum $status, string $message, $student = null, $rfid = null): array
    {
        $response = [
            'status' => $status->value,
            'message' => $message,
            'type' => null,
            'attendance_status' => null,
            'timestamp' => now()->toISOString()
        ];

        if ($student) {
            $response['student'] = $this->formatStudentData($student);
        }

        if ($rfid) {
            $response['rfid'] = $this->formatRfidData($rfid);
        }

        return $response;
    }

    private function createDuplicateResponse($student, $attendance, TapTypeEnum $type, string $message): array
    {
        return [
            'status' => TapStatusEnum::DUPLICATE->value,
            'message' => $message,
            'student' => $this->formatStudentData($student),
            'attendance' => $attendance,
            'type' => $type->value,
            'attendance_status' => $attendance->status,
            'timestamp' => now()->toISOString()
        ];
    }

    private function formatStudentData($student): array
    {
        $activeClassroom = $this->getActiveClassroom($student);

        return [
            'id' => $student->id,
            'name' => $student->user->name,
            'nisn' => $student->nisn,
            'classroom' => $activeClassroom ? [
                'id' => $activeClassroom->id,
                'name' => $activeClassroom->name,
                'major' => $activeClassroom->major->code,
                'level_class' => $activeClassroom->levelClass->name,
            ] : null,
        ];
    }

    private function formatRfidData($rfid): array
    {
        return [
            'id' => $rfid->id,
            'rfid' => $rfid->rfid,
        ];
    }

    private function getEnumValue($value, string $enumClass)
    {
        if ($value instanceof $enumClass) {
            return $value->value;
        }

        return $value;
    }
}