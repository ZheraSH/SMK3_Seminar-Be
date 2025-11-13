<?php

namespace App\Enums;

enum StudentStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case GRADUATED = 'graduated';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Aktif',
            self::INACTIVE => 'Tidak Aktif',
            self::GRADUATED => 'Lulus',
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
            self::GRADUATED->value => 'Lulus',
        ];
    }

    public function canAttend(): bool
    {
        return match($this) {
            self::ACTIVE => true,
            self::INACTIVE, self::GRADUATED => false,
        };
    }
}