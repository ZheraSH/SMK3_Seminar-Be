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
            self::SICK => 'sakit',
            self::PERMISSION => 'izin',
            self::DISPENSATION => 'dispensasi',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}