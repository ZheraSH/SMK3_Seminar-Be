<?php

namespace App\Enums;

enum AttendanceStatusEnum: string
{
    case PRESENT = 'hadir';
    case LATE = 'terlambat';
    case ALPHA = 'alpha';
    case LEAVE = 'izin';
    case SICK = 'sakit';

    public function label(): string
    {
        return match($this) {
            self::PRESENT => 'Hadir',
            self::LATE => 'Terlambat',
            self::ALPHA => 'Alpha',
            self::LEAVE => 'Izin',
            self::SICK => 'Sakit',
        };
    }
}