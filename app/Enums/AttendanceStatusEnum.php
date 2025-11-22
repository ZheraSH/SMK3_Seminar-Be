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

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function toArray(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->value] = $case->label();
        }
        return $array;
    }

    public static function getPresentStatuses(): array
    {
        return [
            self::PRESENT->value,
            self::LATE->value,
        ];
    }

    public static function getAbsentStatuses(): array
    {
        return [
            self::ALPHA->value,
            self::LEAVE->value,
            self::SICK->value,
        ];
    }
}