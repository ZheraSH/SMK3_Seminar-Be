<?php

namespace App\Enums;

enum PermissionTypeEnum: string
{
    case SICK = 'sick';
    case PERMISSION = 'permission';
    case DISPENSATION = 'dispensation';

    public function label(): string
    {
        return match ($this) {
            self::SICK => 'Sakit',
            self::PERMISSION => 'Izin',
            self::DISPENSATION => 'Dispensasi',
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