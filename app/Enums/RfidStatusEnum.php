<?php

namespace App\Enums;

enum RfidStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Aktif',
            self::INACTIVE => 'Tidak Aktif',
        };
    }
    
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function toArray(): array
    {
        return [
            self::ACTIVE->value => 'Aktif',
            self::INACTIVE->value => 'Tidak Aktif',
        ];
    }
}