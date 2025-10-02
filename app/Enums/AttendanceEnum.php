<?php

namespace App\Enums;

enum AttendanceEnum: string
{
    case PRESENT = 'present';
    case LATE = 'late';
    case SICK = 'sick';
    case ALPHA = 'alpha';
    case PERMIT = 'permit';

    public function label(): string
    {
        return match ($this) {
            self::PRESENT => 'masuk',
            self::LATE => 'telat',
            self::SICK => 'sakit',
            self::ALPHA => 'alpha',
            self::PERMIT => 'izin',
        };
    }
    public function color(): string
    {
        return match ($this) {
            self::PRESENT => 'bg-light-success text-success',
            self::LATE => 'bg-light-warning text-warning',
            self::SICK => 'bg-light-info text-info',
            self::ALPHA => 'bg-light-danger text-danger',
            self::PERMIT => 'bg-light-secondary text-secondary',
        };
    }
}
