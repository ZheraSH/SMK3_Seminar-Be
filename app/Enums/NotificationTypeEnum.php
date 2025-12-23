<?php

namespace App\Enums;

enum NotificationTypeEnum: string
{
    case ATTENDANCE_SUCCESS = 'attendance_success';
    case ATTENDANCE_LATE = 'attendance_late';
    case ATTENDANCE_ABSENT = 'attendance_absent';
    case RFID_INVALID = 'rfid_invalid';
    case HOLIDAY_NOTICE = 'holiday_notice';

    public function label(): string
    {
        return match ($this) {
            self::ATTENDANCE_SUCCESS => 'Absen Berhasil',
            self::ATTENDANCE_LATE => 'Keterlambatan',
            self::ATTENDANCE_ABSENT => 'Ketidakhadiran',
            self::RFID_INVALID => 'Kartu RFID Tidak Valid',
            self::HOLIDAY_NOTICE => 'Pemberitahuan Libur',
        };
    }

    public function getMessageTemplate(): string
    {
        return match ($this) {
            self::ATTENDANCE_SUCCESS =>
                'Ananda {student_name} telah berhasil absen {attendance_type} pada {time}',
            self::ATTENDANCE_LATE =>
                'Ananda {student_name} terlambat absen {attendance_type} pada {time}',
            self::ATTENDANCE_ABSENT =>
                'Ananda {student_name} tidak hadir pada tanggal {date}',
            self::RFID_INVALID =>
                'Kartu RFID {rfid_number} tidak valid atau tidak terdaftar',
            self::HOLIDAY_NOTICE =>
                'Pemberitahuan: Besok {date} adalah hari libur {holiday_name}',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function toArray(): array
    {
        $data = [];
        foreach (self::cases() as $case) {
            $data[$case->value] = $case->label();
        }

        return $data;
    }
}