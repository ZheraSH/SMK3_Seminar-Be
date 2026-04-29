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
    private RfidRepository $rfidRepository;
    private AttendanceRepository $attendanceRepository;
    private AttendanceRuleRepository $attendanceRuleRepository;
    private LessonScheduleRepository $lessonScheduleRepository;

    public function __construct(RfidRepository $rfidRepository, AttendanceRepository $attendanceRepository, AttendanceRuleRepository $attendanceRuleRepository, LessonScheduleRepository $lessonScheduleRepository)
    {
        $this->rfidRepository = $rfidRepository;
        $this->attendanceRepository = $attendanceRepository;
        $this->attendanceRuleRepository = $attendanceRuleRepository;
        $this->lessonScheduleRepository = $lessonScheduleRepository;
    }

    public function processBulkUpload(array $attendances): array
    {
        $saved   = 0;
        $skipped = 0;
        $details = [];

        $ruleCache = [];

        foreach ($attendances as $item) {
            $rfidNumber   = $item['rfid']          ?? null;
            $checkinRaw   = $item['checkin_time']  ?? null;
            $checkoutRaw  = $item['checkout_time'] ?? null;

            if (!$rfidNumber || (!$checkinRaw && !$checkoutRaw)) {
                $skipped++;
                $details[] = ['rfid' => $rfidNumber, 'status' => 'skipped', 'reason' => 'Data tidak lengkap'];
                continue;
            }

            $rfid = $this->rfidRepository->getActiveByRfidNumber($rfidNumber);
            if (!$rfid) {
                $skipped++;
                $details[] = ['rfid' => $rfidNumber, 'status' => 'skipped', 'reason' => 'RFID tidak ditemukan atau tidak aktif'];
                continue;
            }

            $student = $rfid->student;
            if (!$student) {
                $skipped++;
                $details[] = ['rfid' => $rfidNumber, 'status' => 'skipped', 'reason' => 'RFID belum terhubung ke siswa'];
                continue;
            }

            $checkinCarbon  = $checkinRaw  ? Carbon::parse($checkinRaw)->timezone('Asia/Jakarta')  : null;
            $checkoutCarbon = $checkoutRaw ? Carbon::parse($checkoutRaw)->timezone('Asia/Jakarta') : null;

            $dateCarbon = $checkinCarbon ?? $checkoutCarbon;
            $dateStr    = $dateCarbon->toDateString();
            $day        = strtolower($dateCarbon->englishDayOfWeek);

            $ruleCache[$day] ??= $this->attendanceRuleRepository->getByDay($day);
            $rule = $ruleCache[$day];

            $status = AttendanceStatusEnum::ALPHA->value;
            if ($checkinCarbon && $rule && !$rule->is_holiday) {
                $expected = TapHelper::parseRuleTimeToCarbon($rule->checkin_start);
                $status   = $expected
                    ? TapHelper::calculateAttendanceStatus($checkinCarbon, $expected)
                    : AttendanceStatusEnum::PRESENT->value;
            }

            $student->loadMissing('classroomStudents');
            $activeClassroom = $student->classroomStudents
                ->where('status', StudentStatusEnum::ACTIVE->value)
                ->first();

            try {
                $record = $this->attendanceRepository->getRFIDAttendanceByStudentAndDate($student->id, $dateStr);

                $attendanceData = [
                    'student_id'           => $student->id,
                    'rfid_id'              => $rfid->id,
                    'classroom_student_id' => $activeClassroom?->id,
                    'date'                 => $dateStr,
                    'checkin_time'         => $checkinCarbon?->toTimeString(),
                    'checkout_time'        => $checkoutCarbon?->toTimeString(),
                    'lesson_order'         => 1,
                    'attendance_type'      => 'rfid',
                    'status'               => $status,
                    'proof'                => AttendanceProofEnum::RFID->value,
                    'is_final'             => true,
                    'is_locked'            => false,
                ];

                if ($record) {
                    $this->attendanceRepository->update($record->id, array_filter($attendanceData, fn($v) => $v !== null));
                } else {
                    $this->attendanceRepository->store($attendanceData);
                }

                $saved++;
                $details[] = [
                    'rfid'         => $rfidNumber,
                    'student_name' => $student->user->name ?? null,
                    'date'         => $dateStr,
                    'checkin_time' => $checkinCarbon?->format('H:i:s'),
                    'checkout_time'=> $checkoutCarbon?->format('H:i:s'),
                    'status'       => $status,
                    'result'       => $record ? 'updated' : 'created',
                ];
            } catch (\Throwable $e) {
                $skipped++;
                $details[] = ['rfid' => $rfidNumber, 'status' => 'skipped', 'reason' => $e->getMessage()];
            }
        }

        return [
            'total'   => count($attendances),
            'saved'   => $saved,
            'skipped' => $skipped,
            'details' => $details,
        ];
    }

    public function processTap(Request $request): array
    {
        return DB::transaction(function () use ($request) {
            $rfidValue = $request->input('rfid');
            if (!$rfidValue) {
                return $this->errorResponse(TapStatusEnum::INVALID, 'RFID is required');
            }

            $rfid = $this->rfidRepository->getByRfidNumber($rfidValue);
            if (!$rfid) {
                return $this->errorResponse(TapStatusEnum::INVALID, 'Kartu RFID tidak valid');
            }

            if (TapHelper::getSafeEnumValue($rfid->status, RfidStatusEnum::class) !== RfidStatusEnum::ACTIVE->value) {
                return $this->errorResponse(TapStatusEnum::INVALID, 'Kartu RFID tidak aktif', null, $rfid);
            }

            $student = $rfid->student;
            if (!$student) {
                return $this->errorResponse(TapStatusEnum::INVALID, 'Kartu RFID belum terhubung ke siswa', null, $rfid);
            }

            $student->load(['user', 'classroomStudents.classroom.major', 'classroomStudents.classroom.levelClass']);

            if (TapHelper::getSafeEnumValue($student->status, StudentStatusEnum::class) !== StudentStatusEnum::ACTIVE->value) {
                return $this->errorResponse(TapStatusEnum::INVALID, 'Siswa tidak aktif', $student, $rfid);
            }

            $activeClassroom = $student->classroomStudents->where('status', StudentStatusEnum::ACTIVE->value)->first();
            if (!$activeClassroom) {
                return $this->errorResponse(TapStatusEnum::INVALID, 'Siswa tidak terdaftar di kelas aktif', $student, $rfid);
            }

            $now  = TapHelper::nowWib();
            $day  = strtolower($now->englishDayOfWeek);
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

            $todayAttendance = $this->attendanceRepository->getRFIDAttendanceByStudentAndDate($student->id, $now->toDateString());

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

        $firstLesson = $this->lessonScheduleRepository
            ->getLessonScheduleClassroomAndDay($activeClassroom->classroom_id ?? $activeClassroom->id, strtolower($now->englishDayOfWeek))
            ->first();

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
            'student_id'           => $student->id,
            'classroom_student_id' => $activeClassroom->id,
            'rfid_id'              => $rfid->id,
            'date'                 => $now->toDateString(),
            'checkin_time'         => $now->toTimeString(),
            'lesson_order'         => 1,
            'attendance_type'      => 'rfid',
            'status'               => $status,
            'proof'                => AttendanceProofEnum::RFID->value,
            'is_final'             => true,
            'is_locked'            => false,
        ];

        if ($todayAttendance) {
            $this->attendanceRepository->update($todayAttendance->id, $data);
            $attendance = $this->attendanceRepository->show($todayAttendance->id);
        } else {
            $new = $this->attendanceRepository->store($data);
            $attendance = $this->attendanceRepository->show($new->id);
        }

        $message = $status === AttendanceStatusEnum::PRESENT->value
            ? 'Hadir tepat waktu'
            : "Terlambat {$minutesLate} menit";

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
            'checkout_time' => $now->toTimeString(),
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

    private function errorResponse($statusEnum, string $message, $student = null, $rfid = null): array
    {
        return [
            'status' => $statusEnum->value,
            'message' => $message,
            'type' => null,
            'attendance_status' => null,
            'requires_manual_attendance' => false,
            'student' => $student ? $this->formatStudent($student) : null,
            'rfid' => $rfid ? $this->formatRfid($rfid) : null,
            'attendance' => null,
            'timestamp' => TapHelper::nowWib()->toISOString(),
            'indonesian_time' => TapHelper::getIndonesianTime(),
        ];
    }

    private function duplicateResponse($student, $attendance, $type, string $message, $rfid = null): array
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

    private function tapRecordResponse($student, $rfid, Carbon $now, string $message): array
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
        $classroom = $student->classroomStudents
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->first()?->classroom ?? null;

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
