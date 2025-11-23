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
        private LessonScheduleInterface $lessonSchedule,
        private StudentInterface $student,
        private AttendanceInterface $attendance,
        private AttendanceRuleInterface $attendanceRule,
        private RfidInterface $rfid
    ) {}

    /**
     * Process RFID tap request
     */
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

        $statusValue = TapHelper::getSafeEnumValue($rfid->status, RfidStatusEnum::class);

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

        $student->load([
            'user',
            'classroomStudents.classroom.major',
            'classroomStudents.classroom.levelClass'
        ]);

        $statusValue = TapHelper::getSafeEnumValue($student->status, StudentStatusEnum::class);

        if ($statusValue !== StudentStatusEnum::ACTIVE->value) {
            throw new \Exception('Siswa tidak aktif', 400);
        }

        if (!$this->getActiveClassroom($student)) {
            throw new \Exception('Siswa tidak terdaftar di kelas aktif', 400);
        }

        return $student;
    }

    private function validateTapTime(): array
    {
        $now = TapHelper::getCurrentTimeWib();
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
            $status = $now->lessThan(Carbon::parse($rule->checkin_start)->timezone('Asia/Jakarta')) 
                ? TapStatusEnum::BEFORE_TIME 
                : TapStatusEnum::AFTER_TIME;

            $message = $now->lessThan(Carbon::parse($rule->checkin_start)->timezone('Asia/Jakarta'))
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

        return $this->processCheckout($student, $rfid, $rule, $now, $todayAttendance);
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

        $expectedTime = Carbon::parse($rule->checkin_start)->timezone('Asia/Jakarta');
        $attendanceStatus = TapHelper::calculateAttendanceStatus($now, $expectedTime);
        $minutesLate = TapHelper::calculateMinutesLate($now, $expectedTime);

        $classroomStudent = $student->classroomStudents
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->first();

        $attendanceData = [
            'student_id' => $student->id,
            'classroom_student_id' => $classroomStudent?->id,
            'classroom_id' => $classroomStudent?->classroom_id,
            'rfid_id' => $rfid->id,
            'date' => $now->toDateString(),
            'checkin_time' => $now->toDateTimeString(),
            'status' => $attendanceStatus,
            'tap_type' => TapTypeEnum::CHECKIN->value,
            'proof' => AttendanceProofEnum::RFID->value,
            'minutes_late' => $minutesLate,
        ];

        if ($todayAttendance) {
            $this->attendance->update($todayAttendance->id, $attendanceData);
            $attendance = $this->attendance->show($todayAttendance->id);
        } else {
            $newAttendance = $this->attendance->store($attendanceData);
            $attendance = $this->attendance->show($newAttendance->id);
        }

        $message = $attendanceStatus === AttendanceStatusEnum::PRESENT->value 
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
            'tap_type' => TapTypeEnum::CHECKOUT->value,
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

        $startTime = Carbon::parse($firstLesson->lessonHour->start)->timezone('Asia/Jakarta');
        $endTime = Carbon::parse($firstLesson->lessonHour->end)->timezone('Asia/Jakarta');

        return $now->between($startTime, $endTime);
    }

    private function getActiveClassroom($student)
    {
        return $student->classroomStudents
            ->where('status', StudentStatusEnum::ACTIVE->value)
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
        ?string $attendanceStatus = null
    ): array {
        return [
            'status' => $status->value,
            'message' => $message,
            'type' => $type->value,
            'attendance_status' => $attendanceStatus,
            'requires_manual_attendance' => false,
            'student' => $this->formatStudentData($student),
            'rfid' => $this->formatRfidData($rfid),
            'attendance' => $this->formatAttendanceData($attendance),
            'timestamp' => TapHelper::getCurrentTimeWib()->toISOString(),
            'indonesian_time' => TapHelper::getIndonesianTime(),
        ];
    }

    private function createErrorResponse(
        TapStatusEnum $status, 
        string $message, 
        $student = null, 
        $rfid = null
    ): array {
        return [
            'status' => $status->value,
            'message' => $message,
            'type' => null,
            'attendance_status' => null,
            'requires_manual_attendance' => false,
            'student' => $student ? $this->formatStudentData($student) : null,
            'rfid' => $rfid ? $this->formatRfidData($rfid) : null,
            'attendance' => null,
            'timestamp' => TapHelper::getCurrentTimeWib()->toISOString(),
            'indonesian_time' => TapHelper::getIndonesianTime(),
        ];
    }

    private function createDuplicateResponse($student, $attendance, TapTypeEnum $type, string $message): array
    {
        return [
            'status' => TapStatusEnum::DUPLICATE->value,
            'message' => $message,
            'type' => $type->value,
            'attendance_status' => TapHelper::getSafeEnumValue($attendance->status, AttendanceStatusEnum::class),
            'requires_manual_attendance' => false,
            'student' => $this->formatStudentData($student),
            'rfid' => null,
            'attendance' => $this->formatAttendanceData($attendance),
            'timestamp' => TapHelper::getCurrentTimeWib()->toISOString(),
            'indonesian_time' => TapHelper::getIndonesianTime(),
        ];
    }

    private function createTapRecordResponse($student, $rfid, Carbon $now, string $message): array
    {
        return [
            'status' => TapStatusEnum::VALID->value,
            'message' => $message,
            'type' => TapTypeEnum::CHECKIN->value,
            'attendance_status' => null,
            'requires_manual_attendance' => true,
            'student' => $this->formatStudentData($student),
            'rfid' => $this->formatRfidData($rfid),
            'attendance' => null,
            'timestamp' => $now->toISOString(),
            'indonesian_time' => TapHelper::getIndonesianTime(),
        ];
    }

    private function formatStudentData($student): array
    {
        $classroom = $this->getActiveClassroom($student);

        return [
            'id' => $student->id,
            'name' => $student->user->name,
            'nisn' => $student->nisn,
            'status' => TapHelper::getSafeEnumValue($student->status, StudentStatusEnum::class),
            'status_label' => TapHelper::getSafeEnumLabel($student->status, StudentStatusEnum::class),
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
            'status' => TapHelper::getSafeEnumValue($rfid->status, RfidStatusEnum::class),
            'status_label' => TapHelper::getSafeEnumLabel($rfid->status, RfidStatusEnum::class),
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
            'checkin_time' => TapHelper::formatDateTimeForDisplay($attendance->checkin_time),
            'checkout_time' => TapHelper::formatDateTimeForDisplay($attendance->checkout_time),
            'status' => TapHelper::getSafeEnumValue($attendance->status, AttendanceStatusEnum::class),
            'status_label' => TapHelper::getSafeEnumLabel($attendance->status, AttendanceStatusEnum::class),
            'tap_type' => TapHelper::getSafeEnumValue($attendance->tap_type, TapTypeEnum::class),
            'tap_type_label' => TapHelper::getSafeEnumLabel($attendance->tap_type, TapTypeEnum::class),
            'proof' => TapHelper::getSafeEnumValue($attendance->proof, AttendanceProofEnum::class),
            'proof_label' => TapHelper::getSafeEnumLabel($attendance->proof, AttendanceProofEnum::class),
            'minutes_late' => $attendance->minutes_late ?? 0,
        ];
    }
}