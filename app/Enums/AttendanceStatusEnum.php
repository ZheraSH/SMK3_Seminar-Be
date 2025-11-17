<?php

namespace App\Enums;

enum AttendanceStatusEnum: string
{
    case ON_TIME = 'hadir_tepat_waktu';
    case LATE = 'terlambat';
    case ABSENT = 'tidak_hadir';
    case LEAVE = 'izin';
    case SICK = 'sakit';

    public function label(): string
    {
        return match($this) {
            self::ON_TIME => 'Hadir Tepat Waktu',
            self::LATE => 'Terlambat',
            self::ABSENT => 'Tidak Hadir',
            self::LEAVE => 'Izin',
            self::SICK => 'Sakit',
        };
    }
}