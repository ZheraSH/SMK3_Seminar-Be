<?php

namespace App\Enums;

enum RfidAttendanceStatusEnum: string
{
    case PRESENT = 'present';
    case LATE = 'late';
    case ALPHA = 'alpha';

    public function label(): string
    {
        return match ($this) {
            self::PRESENT => 'Masuk',
            self::LATE => 'Terlambat',
            self::ALPHA => 'Alpa',
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
