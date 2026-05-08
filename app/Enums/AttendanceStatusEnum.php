<?php

namespace App\Enums;

enum AttendanceStatusEnum: string
{
    case SICK = 'sick';
    case ALPHA = 'alpha';
    case PRESENT = 'present';
    case PERMISSION = 'permission';

    public function label(): string
    {
        return match ($this) {
            self::SICK => 'Sakit',
            self::ALPHA => 'Alpha',
            self::PRESENT => 'Hadir',
            self::PERMISSION => 'Izin',
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