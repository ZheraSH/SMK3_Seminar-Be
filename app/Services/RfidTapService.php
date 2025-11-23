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
    private AttendanceInterface $attendance;
    private AttendanceRuleInterface $attendanceRule;
    private StudentInterface $student;
    private RfidInterface $rfid;
    private LessonScheduleInterface $lessonSchedule;

    public function __construct(
        LessonScheduleInterface $lessonSchedule,
        StudentInterface $student,
        AttendanceInterface $attendance,
        AttendanceRuleInterface $attendanceRule,
        RfidInterface $rfid
    ) {
        $this->attendance = $attendance;
        $this->attendanceRule = $attendanceRule;
        $this->student = $student;
        $this->rfid = $rfid;
        $this->lessonSchedule = $lessonSchedule;
    }

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

            return $this->processAttendance(
                $student,
                $rfid,
                $timeValidation['rule'],
                $timeValidation['now'],
                $timeValidation['isCheckinTime'],
                $timeValidation['isCheckoutTime']
            );
        });
    }

    private function validateRfid(string $rfidNumber)
    {
        $rfid = $this->rfid->getByRfidNumber($rfidNumber);

        if (!$rfid) {
            throw new \Exception('Kartu RFID tidak valid', 400);
        }

        if ($rfid->status !== RfidStatusEnum::ACTIVE) {
            throw new \Exception('Kartu RFID tidak aktif. Status: ' . $rfid->status->label(), 400);
        }

        return $rfid;
    }

    private function validateStudent($rfid)
    {
        $student = $rfid->student;

        if (!$student) {
            throw new \Exception('Kartu RFID belum terhubung ke siswa', 400);
        }

        $student->load([
            'user',
            'classroomStudents.classroom.major',
            'classroomStudents.classroom.levelClass'
        ]);

        if ($student->status !== StudentStatusEnum::ACTIVE) {
            throw new \Exception('Siswa tidak aktif. Status: ' . $student->status->label(), 400);
        }

        if (!$this->getActiveClassroom($student)) {
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
                'message' => 'Tidak ada aturan absensi untuk hari ini (' . TapHelper::getIndonesianDay() . ')'
            ];
        }

        if ($rule->is_holiday) {
            return [
                'valid' => false,
                'status' => TapStatusEnum::INVALID,
                'message' => 'Hari ini libur (' . TapHelper::getIndonesianDay() . ')'
            ];
        }

        $isCheckinTime = TapHelper::isWithinTimeRange($now, $rule->checkin_start, $rule->checkin_end);
        $isCheckoutTime = TapHelper::isWithinTimeRange($now, $rule->checkout_start, $rule->checkout_end);

        if (!$isCheckinTime && !$isCheckoutTime) {
            $status = $now->lessThan(Carbon::parse($rule->checkin_start)) 
                ? TapStatusEnum::BEFORE_TIME 
                : TapStatusEnum::AFTER_TIME;

            $message = $now->lessThan(Carbon::parse($rule->checkin_start))
                ? 'Belum waktu absen. Jam absen masuk: ' . TapHelper::formatTimeForDisplay($rule->checkin_start)
                : 'Sudah lewat waktu absen';

            return [
                'valid' => false,
                'status' => $status,
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

    private function processAttendance($student, $rfid, $rule, Carbon $now, bool $isCheckinTime, bool $isCheckoutTime): array
    {
        $todayAttendance = $this->attendance->getTodayByStudent($student->id);

        if ($isCheckinTime) {
            return $this->processCheckin($student, $rfid, $rule, $now, $todayAttendance);
        }

        if ($isCheckoutTime) {
            return $this->processCheckout($student, $rfid, $rule, $now, $todayAttendance);
        }

        throw new \Exception('Waktu tap tidak valid');
    }

    private function processCheckin($student, $rfid, $rule, Carbon $now, $todayAttendance)
    {
        if ($todayAttendance && TapHelper::isDuplicateTap($todayAttendance, $now, TapTypeEnum::CHECKIN->value)) {
            return $this->createDuplicateResponse(
                $student,
                $todayAttendance,
                TapTypeEnum::CHECKIN,
                'Absen masuk sudah tercatat hari ini'
            );
        }

        $isFirstLesson = $this->isFirstLessonTime($student, $now);

        if (!$isFirstLesson) {
            return $this->createTapRecordResponse(
                $student,
                $rfid,
                $now,
                'Tap berhasil dicatat. Absensi akan dilakukan oleh guru pada jam pelajaran pertama'
            );
        }

        $expectedTime = Carbon::parse($rule->checkin_start);
        $attendanceStatus = TapHelper::calculateAttendanceStatus($now, $expectedTime);
        $minutesLate = TapHelper::calculateMinutesLate($now, $expectedTime);

        $classroomStudent = $student->classroomStudents
            ->where('status', StudentStatusEnum::ACTIVE)
            ->first();

        $attendanceData = [
            'student_id' => $student->id,
            'classroom_student_id' => $classroomStudent?->id,
            'classroom_id' => $classroomStudent?->classroom_id,
            'rfid_id' => $rfid->id,
            'date' => $now->toDateString(),
            'checkin_time' => $now->toDateTimeString(),
            'status' => $attendanceStatus,
            'tap_type' => TapTypeEnum::CHECKIN,
            'proof' => AttendanceProofEnum::RFID,
            'minutes_late' => $minutesLate,
        ];

        if ($todayAttendance) {
            $this->attendance->update($todayAttendance->id, $attendanceData);
            $attendance = $this->attendance->show($todayAttendance->id);
        } else {
            $newAttendance = $this->attendance->store($attendanceData);
            $attendance = $this->attendance->show($newAttendance->id);
        }

        $message = $attendanceStatus === AttendanceStatusEnum::PRESENT 
            ? 'Hadir tepat waktu' 
            : 'Terlambat ' . $minutesLate . ' menit';

        return $this->createSuccessResponse(
            TapStatusEnum::VALID,
            $message,
            $student,
            $rfid,
            $attendance,
            TapTypeEnum::CHECKIN,
            $attendanceStatus
        );
    }

    private function processCheckout($student, $rfid, $rule, Carbon $now, $todayAttendance)
    {
        if (!$todayAttendance) {
            throw new \Exception('Belum melakukan absen masuk hari ini');
        }

        if ($todayAttendance->checkout_time) {
            return $this->createDuplicateResponse(
                $student,
                $todayAttendance,
                TapTypeEnum::CHECKOUT,
                'Absen pulang sudah tercatat hari ini'
            );
        }

        $this->attendance->update($todayAttendance->id, [
            'checkout_time' => $now->toDateTimeString(),
            'tap_type' => TapTypeEnum::CHECKOUT,
        ]);

        $updatedAttendance = $this->attendance->show($todayAttendance->id);

        return $this->createSuccessResponse(
            TapStatusEnum::VALID,
            'Absen pulang berhasil',
            $student,
            $rfid,
            $updatedAttendance,
            TapTypeEnum::CHECKOUT
        );
    }

    private function isFirstLessonTime($student, Carbon $now): bool
    {
        $classroom = $this->getActiveClassroom($student);

        if (!$classroom) {
            return false;
        }

        $day = strtolower($now->englishDayOfWeek);
        $firstLesson = $this->lessonSchedule->getFirstLessonByClassroomAndDay($classroom->id, $day);

        if (!$firstLesson || !$firstLesson->lessonHour) {
            return false;
        }

        $startTime = Carbon::parse($firstLesson->lessonHour->start);
        $endTime = Carbon::parse($firstLesson->lessonHour->end);

        return $now->between($startTime, $endTime);
    }

    private function getActiveClassroom($student)
    {
        return $student->classroomStudents
            ->where('status', StudentStatusEnum::ACTIVE)
            ->first()
            ?->classroom;
    }

    private function createSuccessResponse(
        TapStatusEnum $status,
        string $message,
        $student,
        $rfid,
        $attendance,
        TapTypeEnum $type,
        ?AttendanceStatusEnum $attendanceStatus = null
    ): array {
        return [
            'status' => $status->value,
            'message' => $message,
            'type' => $type->value,
            'attendance_status' => $attendanceStatus?->value,
            'student' => $this->formatStudentData($student),
            'rfid' => $this->formatRfidData($rfid),
            'attendance' => $this->formatAttendanceData($attendance),
            'timestamp' => now()->toISOString(),
        ];
    }

    private function createErrorResponse(TapStatusEnum $status, string $message, $student = null, $rfid = null): array
    {
        return [
            'status' => $status->value,
            'message' => $message,
            'student' => $student ? $this->formatStudentData($student) : null,
            'rfid' => $rfid ? $this->formatRfidData($rfid) : null,
            'timestamp' => now()->toISOString(),
        ];
    }

    private function createDuplicateResponse($student, $attendance, TapTypeEnum $type, string $message): array
    {
        return [
            'status' => TapStatusEnum::DUPLICATE->value,
            'message' => $message,
            'type' => $type->value,
            'attendance' => $this->formatAttendanceData($attendance),
            'student' => $this->formatStudentData($student),
            'attendance_status' => $attendance->status->value,
            'timestamp' => now()->toISOString(),
        ];
    }

    private function createTapRecordResponse($student, $rfid, Carbon $now, string $message): array
    {
        return [
            'status' => TapStatusEnum::VALID->value,
            'message' => $message,
            'type' => TapTypeEnum::CHECKIN->value,
            'requires_manual_attendance' => true,
            'student' => $this->formatStudentData($student),
            'rfid' => $this->formatRfidData($rfid),
            'attendance' => null,
            'timestamp' => $now->toISOString(),
        ];
    }

    private function formatStudentData($student): array
    {
        $classroom = $this->getActiveClassroom($student);

        return [
            'id' => $student->id,
            'name' => $student->user->name,
            'nisn' => $student->nisn,
            'status' => $student->status->value,
            'status_label' => $student->status->label(),
            'classroom' => $classroom ? [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'major' => $classroom->major->code,
                'level_class' => $classroom->levelClass->name,
            ] : null,
        ];
    }

    private function formatRfidData($rfid): array
    {
        return [
            'id' => $rfid->id,
            'rfid' => $rfid->rfid,
            'status' => $rfid->status->value,
            'status_label' => $rfid->status->label(),
        ];
    }

    private function formatAttendanceData($attendance): ?array
    {
        if (!$attendance) {
            return null;
        }

        return [
            'id' => $attendance->id,
            'date' => $attendance->date,
            'checkin_time' => $attendance->checkin_time,
            'checkout_time' => $attendance->checkout_time,
            'status' => $attendance->status->value,
            'status_label' => $attendance->status->label(),
            'tap_type' => $attendance->tap_type->value,
            'tap_type_label' => $attendance->tap_type->label(),
            'proof' => $attendance->proof->value,
            'proof_label' => $attendance->proof->label(),
            'minutes_late' => $attendance->minutes_late ?? 0,
        ];
    }
}