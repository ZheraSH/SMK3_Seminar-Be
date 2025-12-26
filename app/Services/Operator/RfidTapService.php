<?php

namespace App\Services\Operator;

use App\Contracts\Repositories\AttendanceRepository;
use App\Contracts\Repositories\Operator\RfidRepository;
use App\Contracts\Repositories\Operator\AttendanceRuleRepository;
use App\Contracts\Repositories\Operator\LessonScheduleRepository;
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
        private RfidRepository $rfidRepository,
        private AttendanceRepository $attendanceRepository,
        private AttendanceRuleRepository $attendanceRuleRepository,
        private LessonScheduleRepository $lessonScheduleRepository
    ) {}

    public function processTap(Request $request): array
    {
        return DB::transaction(function () use ($request) {
            $rfidValue = $request->input('rfid');
            if (!$rfidValue) {
                return $this->errorResponse(TapStatusEnum::INVALID, 'RFID is required');
            }

            // validate rfid
            $rfid = $this->rfidRepository->getByRfidNumber($rfidValue);
            if (!$rfid) return $this->errorResponse(TapStatusEnum::INVALID, 'Kartu RFID tidak valid');

            if (TapHelper::getSafeEnumValue($rfid->status, RfidStatusEnum::class) !== RfidStatusEnum::ACTIVE->value) {
                return $this->errorResponse(TapStatusEnum::INVALID, 'Kartu RFID tidak aktif', null, $rfid);
            }

            // student
            $student = $rfid->student;
            if (!$student) return $this->errorResponse(TapStatusEnum::INVALID, 'Kartu RFID belum terhubung ke siswa', null, $rfid);

            $student->load(['user','classroomStudents.classroom.major','classroomStudents.classroom.levelClass']);

            if (TapHelper::getSafeEnumValue($student->status, StudentStatusEnum::class) !== StudentStatusEnum::ACTIVE->value) {
                return $this->errorResponse(TapStatusEnum::INVALID, 'Siswa tidak aktif', $student, $rfid);
            }

            $activeClassroom = $student->classroomStudents->where('status', StudentStatusEnum::ACTIVE->value)->first();
            if (!$activeClassroom) {
                return $this->errorResponse(TapStatusEnum::INVALID, 'Siswa tidak terdaftar di kelas aktif', $student, $rfid);
            }

            // time rules
            $now = TapHelper::nowWib();
            $day = strtolower($now->englishDayOfWeek);
            $rule = $this->attendanceRuleRepository->getByDay($day);

            if (!$rule) {
                return $this->errorResponse(TapStatusEnum::INVALID, 'Tidak ada aturan absensi untuk hari ini', $student, $rfid);
            }

            if ($rule->is_holiday) {
                return $this->errorResponse(TapStatusEnum::INVALID, 'Hari ini libur', $student, $rfid);
            }

            $isCheckinTime = TapHelper::isWithinTimeRange($now, $rule->checkin_start, $rule->checkin_end);
            $isCheckoutTime = TapHelper::isWithinTimeRange($now, $rule->checkout_start, $rule->checkout_end);

            if (!$isCheckinTime && !$isCheckoutTime) {
                $startDisplay = TapHelper::parseRuleTimeToCarbon($rule->checkin_start)?->format('H:i') ?? '-';
                $status = $now->lessThan(TapHelper::parseRuleTimeToCarbon($rule->checkin_start) ?? $now) ? TapStatusEnum::BEFORE_TIME : TapStatusEnum::AFTER_TIME;
                $message = $status === TapStatusEnum::BEFORE_TIME ? 'Belum waktu absen. Jam absen masuk: ' . $startDisplay : 'Sudah lewat waktu absen';
                return $this->errorResponse($status, $message, $student, $rfid);
            }

            // fetch today's attendance (repo must implement getTodayByStudent)
            $todayAttendance = $this->attendanceRepository->getTodayByStudent($student->id);

            if ($isCheckinTime) {
                return $this->handleCheckin($student, $rfid, $rule, $now, $todayAttendance, $activeClassroom);
            }

            return $this->handleCheckout($student, $rfid, $rule, $now, $todayAttendance);
        });
    }

    private function handleCheckin($student, $rfid, $rule, Carbon $now, $todayAttendance, $activeClassroom): array
    {
        if ($todayAttendance && TapHelper::isDuplicateTap($todayAttendance, $now, TapTypeEnum::CHECKIN->value)) {
            return $this->duplicateResponse($student, $todayAttendance, TapTypeEnum::CHECKIN, 'Absen masuk sudah tercatat sebelumnya', $rfid);
        }

        // check first lesson window (safe)
        $firstLesson = $this->lessonScheduleRepository->getFirstLessonByClassroomAndDay($activeClassroom->classroom_id ?? $activeClassroom->id, strtolower($now->englishDayOfWeek));
        $isFirstLesson = false;
        if ($firstLesson && $firstLesson->lessonHour) {
            $start = TapHelper::parseRuleTimeToCarbon($firstLesson->lessonHour->start);
            $end = TapHelper::parseRuleTimeToCarbon($firstLesson->lessonHour->end);
            $isFirstLesson = $start && $end && $now->between($start, $end);
        }

        if (!$isFirstLesson) {
            return $this->tapRecordResponse($student, $rfid, $now, 'Tap berhasil. Absensi akan dilakukan guru pada jam pelajaran pertama');
        }

        $expected = TapHelper::parseRuleTimeToCarbon($rule->checkin_start) ?? $now;
        $status = TapHelper::calculateAttendanceStatus($now, $expected);
        $minutesLate = TapHelper::calculateMinutesLate($now, $expected);

        $data = [
            'student_id' => $student->id,
            'classroom_student_id' => $activeClassroom->id,
            'classroom_id' => $activeClassroom->classroom_id ?? ($activeClassroom->classroom->id ?? null),
            'rfid_id' => $rfid->id,
            'date' => $now->toDateString(),
            'checkin_time' => $now->toDateTimeString(),
            'status' => $status,
            'tap_type' => TapTypeEnum::CHECKIN->value,
            'proof' => AttendanceProofEnum::RFID->value,
            'minutes_late' => $minutesLate,
        ];

        if ($todayAttendance) {
            $attendance = $this->attendanceRepository->update($todayAttendance->id, $data);
            $attendance = $this->attendanceRepository->show($todayAttendance->id);
        } else {
            $new = $this->attendanceRepository->store($data);
            $attendance = $this->attendanceRepository->show($new->id);
        }

        $message = $status === AttendanceStatusEnum::PRESENT->value ? 'Hadir tepat waktu' : ("Terlambat {$minutesLate} menit");

        return $this->successResponse(TapStatusEnum::VALID, $message, $student, $rfid, $attendance, TapTypeEnum::CHECKIN);
    }

    private function handleCheckout($student, $rfid, $rule, Carbon $now, $todayAttendance): array
    {
        if (!$todayAttendance) {
            return $this->errorResponse(TapStatusEnum::INVALID, 'Belum melakukan absen masuk hari ini', $student, $rfid);
        }

        if ($todayAttendance->checkout_time && TapHelper::isDuplicateTap($todayAttendance, $now, TapTypeEnum::CHECKOUT->value)) {
            return $this->duplicateResponse($student, $todayAttendance, TapTypeEnum::CHECKOUT, 'Absen pulang sudah tercatat sebelumnya', $rfid);
        }

        $this->attendanceRepository->update($todayAttendance->id, [
            'checkout_time' => $now->toDateTimeString(),
            'tap_type' => TapTypeEnum::CHECKOUT->value,
        ]);

        $updated = $this->attendanceRepository->show($todayAttendance->id);
        return $this->successResponse(TapStatusEnum::VALID, 'Absen pulang berhasil', $student, $rfid, $updated, TapTypeEnum::CHECKOUT);
    }

    private function successResponse($statusEnum, string $message, $student, $rfid, $attendance, $type): array
    {
        return [
            'status' => $statusEnum->value,
            'message' => $message,
            'type' => $type->value,
            'attendance_status' => $attendance ? TapHelper::getSafeEnumValue($attendance->status, \App\Enums\AttendanceStatusEnum::class) : null,
            'requires_manual_attendance' => false,
            'student' => $this->formatStudent($student),
            'rfid' => $this->formatRfid($rfid),
            'attendance' => $this->formatAttendance($attendance),
            'timestamp' => TapHelper::nowWib()->toISOString(),
            'indonesian_time' => TapHelper::getIndonesianTime(),
        ];
    }

    private function errorResponse($statusEnum, string $message, $student = null, $rfid = null)
    {
        $now = TapHelper::nowWib();
        return [
            'status' => $statusEnum->value,
            'message' => $message,
            'type' => null,
            'attendance_status' => null,
            'requires_manual_attendance' => false,
            'student' => $student ? $this->formatStudent($student) : null,
            'rfid' => $rfid ? $this->formatRfid($rfid) : null,
            'attendance' => null,
            'timestamp' => $now->toISOString(),
            'indonesian_time' => TapHelper::getIndonesianTime(),
        ];
    }

    private function duplicateResponse($student, $attendance, $type, $message, $rfid = null)
    {
        return [
            'status' => TapStatusEnum::DUPLICATE->value,
            'message' => $message,
            'type' => $type->value,
            'attendance_status' => TapHelper::getSafeEnumValue($attendance->status, \App\Enums\AttendanceStatusEnum::class),
            'requires_manual_attendance' => false,
            'student' => $this->formatStudent($student),
            'rfid' => $rfid ? $this->formatRfid($rfid) : null,
            'attendance' => $this->formatAttendance($attendance),
            'timestamp' => TapHelper::nowWib()->toISOString(),
            'indonesian_time' => TapHelper::getIndonesianTime(),
        ];
    }

    private function tapRecordResponse($student, $rfid, Carbon $now, string $message)
    {
        return [
            'status' => TapStatusEnum::VALID->value,
            'message' => $message,
            'type' => TapTypeEnum::CHECKIN->value,
            'attendance_status' => null,
            'requires_manual_attendance' => true,
            'student' => $this->formatStudent($student),
            'rfid' => $this->formatRfid($rfid),
            'attendance' => null,
            'timestamp' => $now->toISOString(),
            'indonesian_time' => TapHelper::getIndonesianTime(),
        ];
    }

    private function formatStudent($student): array
    {
        $classroom = $student->classroomStudents->where('status', StudentStatusEnum::ACTIVE->value)->first()?->classroom ?? null;
        return [
            'id' => $student->id,
            'name' => $student->user->name ?? null,
            'nisn' => $student->nisn ?? null,
            'status' => TapHelper::getSafeEnumValue($student->status, StudentStatusEnum::class),
            'status_label' => TapHelper::getSafeEnumLabel($student->status, StudentStatusEnum::class),
            'classroom' => $classroom ? [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'major' => $classroom->major->code ?? null,
                'level_class' => $classroom->levelClass->name ?? null,
            ] : null,
        ];
    }

    private function formatRfid($rfid): array
    {
        return [
            'id' => $rfid->id,
            'rfid' => $rfid->rfid,
            'status' => TapHelper::getSafeEnumValue($rfid->status, RfidStatusEnum::class),
            'status_label' => TapHelper::getSafeEnumLabel($rfid->status, RfidStatusEnum::class),
        ];
    }

    private function formatAttendance($attendance): ?array
    {
        if (!$attendance) return null;
        return [
            'id' => $attendance->id,
            'date' => $attendance->date,
            'checkin_time' => TapHelper::formatDateTimeForDisplay($attendance->checkin_time),
            'checkout_time' => TapHelper::formatDateTimeForDisplay($attendance->checkout_time),
            'status' => TapHelper::getSafeEnumValue($attendance->status, AttendanceStatusEnum::class),
            'status_label' => TapHelper::getSafeEnumLabel($attendance->status, AttendanceStatusEnum::class),
            'minutes_late' => $attendance->minutes_late ?? 0,
        ];
    }
}
